<?php

namespace App\Http\Controllers;

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
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], $this->messages());

        $purchase = DB::transaction(function () use ($data, $request) {
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
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $subtotal = round($qty * $cost, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $qty);

                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Pembelian {$purchase->purchase_number} berhasil disimpan. Stok produk sudah bertambah.");
    }

    public function show(Request $request, Purchase $purchase)
    {
        $user = $request->user();

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

    private function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak tersedia.',
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.min' => 'Tambahkan minimal satu produk.',
            'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak tersedia.',
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
