<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // Staff hanya melihat pembelian yang dia catat sendiri
        $scope = Purchase::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        $purchases = (clone $scope)
            ->with(['supplier', 'user'])
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('purchase_number', 'like', "%{$q}%")
                      ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $s) => $query->where('status', $s))
            ->when($filters['from'] ?? null,
                fn ($query, $d) => $query->where('purchased_at', '>=', $d . ' 00:00:00'))
            ->when($filters['to'] ?? null,
                fn ($query, $d) => $query->where('purchased_at', '<=', $d . ' 23:59:59'))
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $monthly = (clone $scope)
            ->where('status', 'completed')
            ->where('purchased_at', '>=', now()->startOfMonth());

        return view('purchases.index', [
            'purchases' => $purchases,
            'countAll' => (clone $scope)->count(),
            'countDone' => (clone $scope)->where('status', 'completed')->count(),
            'countCancelled' => (clone $scope)->where('status', 'cancelled')->count(),
            'monthCount' => (clone $monthly)->count(),
            'monthSum' => (clone $monthly)->sum('total'),
        ]);
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')
                ->get(['id', 'sku', 'name', 'purchase_price', 'stock'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => $p->sku . ' - ' . $p->name,
                    'cost' => (int) $p->purchase_price,
                    'stock' => $p->stock,
                ]),
        ]);
    }

    public function store(Request $request)
    {
        // tiap baris: produk yang sudah ada (mode existing) atau produk baru (mode new)
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.mode' => ['nullable', 'in:existing,new'],
            'items.*.product_id' => [
                'required_unless:items.*.mode,new', 'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.new_name' => ['required_if:items.*.mode,new', 'nullable', 'string', 'min:2', 'max:150'],
            'items.*.new_category_id' => ['required_if:items.*.mode,new', 'nullable', 'integer', 'exists:categories,id'],
            'items.*.new_selling_price' => ['required_if:items.*.mode,new', 'nullable', 'numeric', 'min:0'],
            'items.*.new_minimum_stock' => ['required_if:items.*.mode,new', 'nullable', 'integer', 'min:0', 'max:100000'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], $this->messages());

        // aturan tambahan khusus produk baru
        $problems = [];
        $seenNames = [];
        $position = 0;

        foreach ($data['items'] as $item) {
            $position++;

            if (($item['mode'] ?? 'existing') !== 'new') {
                continue;
            }

            $name = trim($item['new_name']);
            $key = mb_strtolower($name);

            if (in_array($key, $seenNames, true)) {
                $problems[] = "Baris {$position}: produk baru '{$name}' dimasukkan dua kali.";
            } elseif (Product::whereRaw('LOWER(name) = ?', [$key])->exists()) {
                $problems[] = "Baris {$position}: produk '{$name}' sudah ada. Pilih dari daftar produk, jangan ditambah sebagai produk baru.";
            }

            if ((float) $item['new_selling_price'] < (float) $item['unit_cost']) {
                $problems[] = "Baris {$position}: harga jual '{$name}' tidak boleh lebih rendah dari harga beli.";
            }

            $seenNames[] = $key;
        }

        if ($problems) {
            throw ValidationException::withMessages(['items' => $problems]);
        }

        $newProducts = 0;

        // Semua langkah di bawah satu paket: gagal satu, batal semua
        $purchase = DB::transaction(function () use ($data, $request, &$newProducts) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'purchase_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $subtotal = round($qty * $cost, 2); // dihitung server

                if (($item['mode'] ?? 'existing') === 'new') {
                    $category = Category::findOrFail($item['new_category_id']);

                    // produk baru: SKU otomatis, stok awal = jumlah yang dibeli
                    $product = Product::create([
                        'category_id' => $category->id,
                        'sku' => $this->generateSku($category),
                        'name' => trim($item['new_name']),
                        'purchase_price' => $cost,
                        'selling_price' => (float) $item['new_selling_price'],
                        'stock' => $qty,
                        'minimum_stock' => (int) $item['new_minimum_stock'],
                    ]);

                    $newProducts++;
                } else {
                    // kunci baris produk selama transaksi berjalan
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    // atomik di database: stock = stock + qty
                    $product->increment('stock', $qty);
                }

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });

        $message = "Pembelian {$purchase->purchase_number} berhasil disimpan. Stok produk sudah bertambah.";

        if ($newProducts > 0) {
            $message .= " {$newProducts} produk baru ditambahkan ke daftar produk.";
        }

        return redirect()->route('purchases.show', $purchase)->with('success', $message);
    }

    public function show(Request $request, Purchase $purchase)
    {
        $user = $request->user();

        // Staff tidak boleh membuka pembelian milik orang lain lewat URL
        abort_unless(
            $user->isAdmin() || $purchase->user_id === $user->id,
            403,
            'Anda tidak memiliki akses ke transaksi ini.'
        );

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                $locked = Purchase::lockForUpdate()->findOrFail($purchase->id);

                if ($locked->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'status' => 'Pembelian ini sudah dibatalkan sebelumnya.',
                    ]);
                }

                foreach ($locked->items as $item) {
                    $product = Product::withTrashed()
                        ->lockForUpdate()
                        ->findOrFail($item->product_id);

                    // stok tidak boleh menjadi negatif
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'stock' => "Pembatalan ditolak: stok {$product->name} saat ini {$product->stock}, "
                                . "kurang dari {$item->quantity} yang harus dikembalikan (sebagian sudah terjual).",
                        ]);
                    }

                    $product->decrement('stock', $item->quantity);
                }

                $locked->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dibatalkan dan stok produk sudah dikurangi kembali.');
    }

    // PB-20261025-0001, urut per hari
    private function generateNumber(): string
    {
        $prefix = 'PB-' . now()->format('Ymd') . '-';

        $last = Purchase::where('purchase_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('purchase_number')
            ->value('purchase_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    // SKU otomatis: awalan dari kategori + nomor lanjutan, misalnya OBT-007
    private function generateSku(Category $category): string
    {
        $prefix = match ($category->name) {
            'Obat Bebas' => 'OBT',
            'Obat Keras' => 'KRS',
            'Vitamin & Suplemen' => 'VIT',
            'Alat Kesehatan' => 'ALK',
            default => $this->prefixFromName($category->name),
        };

        // nomor terbesar yang sudah dipakai, termasuk produk yang sudah dihapus
        $max = Product::withTrashed()
            ->where('sku', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->pluck('sku')
            ->map(fn ($sku) => (int) substr($sku, strlen($prefix) + 1))
            ->max() ?? 0;

        return $prefix . '-' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    // untuk kategori baru buatan Admin: tiga huruf pertama nama kategori
    private function prefixFromName(string $name): string
    {
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));

        return $letters === '' ? 'PRD' : str_pad(substr($letters, 0, 3), 3, 'X');
    }

    private function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak tersedia.',
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.min' => 'Tambahkan minimal satu produk.',
            'items.*.mode.in' => 'Jenis baris produk tidak valid.',
            'items.*.product_id.required_unless' => 'Pilih produk pada setiap baris, atau pilih Produk baru.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'items.*.new_name.required_if' => 'Nama produk baru wajib diisi.',
            'items.*.new_name.min' => 'Nama produk baru minimal 2 karakter.',
            'items.*.new_name.max' => 'Nama produk baru maksimal 150 karakter.',
            'items.*.new_category_id.required_if' => 'Kategori produk baru wajib dipilih.',
            'items.*.new_category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'items.*.new_selling_price.required_if' => 'Harga jual produk baru wajib diisi.',
            'items.*.new_selling_price.numeric' => 'Harga jual harus berupa angka.',
            'items.*.new_selling_price.min' => 'Harga jual tidak boleh negatif.',
            'items.*.new_minimum_stock.required_if' => 'Stok minimum produk baru wajib diisi.',
            'items.*.new_minimum_stock.integer' => 'Stok minimum harus berupa bilangan bulat.',
            'items.*.new_minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
            'items.*.new_minimum_stock.max' => 'Stok minimum maksimal 100.000.',
            'items.*.quantity.required' => 'Jumlah wajib diisi pada setiap baris.',
            'items.*.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.max' => 'Jumlah maksimal 100.000 per baris.',
            'items.*.unit_cost.required' => 'Harga beli wajib diisi pada setiap baris.',
            'items.*.unit_cost.numeric' => 'Harga beli harus berupa angka.',
            'items.*.unit_cost.min' => 'Harga beli tidak boleh negatif.',
        ];
    }
}