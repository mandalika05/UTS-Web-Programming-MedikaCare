<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $scope = Sale::query()->when(! $user->isAdmin(), function ($q) use ($user) {
            $q->where(function ($w) use ($user) {
                $w->where('user_id', $user->id)
                    ->orWhere('status', 'pending');
            });
        });

        $sales = (clone $scope)
            ->with('user')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('sale_number', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%");
                });
            })
            ->when(
                $filters['status'] ?? null,
                fn ($query, $s) => $query->where('status', $s)
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $d) => $query->where('created_at', '>=', $d . ' 00:00:00')
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $d) => $query->where('created_at', '<=', $d . ' 23:59:59')
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $today = (clone $scope)
            ->where('status', 'completed')
            ->where('sold_at', '>=', now()->startOfDay());

        return view('sales.index', [
            'sales' => $sales,
            'countAll' => (clone $scope)->count(),
            'countPending' => (clone $scope)->where('status', 'pending')->count(),
            'countDone' => (clone $scope)->where('status', 'completed')->count(),
            'countCancelled' => (clone $scope)->where('status', 'cancelled')->count(),
            'todaySum' => (clone $today)->sum('total'),
            'todayCount' => (clone $today)->count(),
        ]);
    }

    public function create()
    {
        return view('sales.create', [
            'customers' => User::where('role', 'customer')
                ->orderBy('name')
                ->get(['id', 'name']),
            'products' => Product::orderBy('name')
                ->get(['id', 'sku', 'name', 'selling_price', 'stock'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => $p->sku . ' - ' . $p->name,
                    'price' => (int) $p->selling_price,
                    'stock' => $p->stock,
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'customer'),
            ],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'in:tunai,transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
        ], $this->messages());

        $customer = filled($data['customer_id'] ?? null)
            ? User::find($data['customer_id'])
            : null;

        $customerName = $customer
            ? $customer->name
            : ($data['customer_name'] ?? null);

        $sale = DB::transaction(function () use (
            $data,
            $request,
            $customer,
            $customerName
        ) {
            $ids = collect($data['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $products = Product::whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $problems = [];

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['quantity'];

                if (! $product) {
                    $problems[] = 'Ada produk yang sudah tidak tersedia.';
                } elseif ($product->stock < $qty) {
                    $problems[] = "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).";
                }
            }

            if ($problems) {
                throw ValidationException::withMessages([
                    'items' => $problems,
                ]);
            }

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'user_id' => $request->user()->id,
                'customer_name' => $customerName,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'completed',
                'payment_method' => $data['payment_method'],
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['quantity'];
                $price = (float) $product->selling_price;
                $subtotal = round($qty * $price, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->sale_number} berhasil disimpan. Stok sudah berkurang.");
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless(
            $this->canView($request->user(), $sale),
            403,
            'Anda tidak memiliki akses ke transaksi ini.'
        );

        $sale->load(['user', 'customer', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Request $request, Sale $sale)
    {
        abort_unless(
            $this->canView($request->user(), $sale),
            403,
            'Anda tidak memiliki akses ke transaksi ini.'
        );

        abort_unless(
            $sale->status === 'completed',
            404,
            'Struk hanya tersedia untuk transaksi yang sudah selesai.'
        );

        $sale->load(['user', 'items.product']);

        return view('sales.receipt', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                $locked = Sale::lockForUpdate()->findOrFail($sale->id);

                if ($locked->status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                    ]);
                }

                // Transaksi selesai hanya dapat dibatalkan dalam 24 jam.
                if ($locked->status === 'completed') {
                    $completedAt = $locked->sold_at ?? $locked->created_at;

                    if ($completedAt->lt(now()->subDay())) {
                        throw ValidationException::withMessages([
                            'status' => 'Transaksi yang sudah selesai lebih dari 24 jam tidak dapat dibatalkan.',
                        ]);
                    }

                    // Stok dikembalikan karena sebelumnya sudah dikurangi.
                    foreach (
                        $locked->items()->orderBy('product_id')->get()
                        as $item
                    ) {
                        Product::withTrashed()
                            ->lockForUpdate()
                            ->find($item->product_id)
                            ?->increment('stock', $item->quantity);
                    }
                }

                $locked->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with(
                'error',
                collect($e->errors())->flatten()->first()
            );
        }

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Transaksi dibatalkan dan stok produk sudah dikembalikan.');
    }

    public function confirm(Request $request, Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale, $request) {
                $locked = Sale::lockForUpdate()->findOrFail($sale->id);

                if ($locked->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Pesanan ini sudah diproses sebelumnya.',
                    ]);
                }

                $items = $locked->items()->orderBy('product_id')->get();

                $products = Product::whereIn('id', $items->pluck('product_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $problems = [];

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);

                    if (! $product) {
                        $problems[] = 'Ada produk pesanan yang sudah tidak tersedia.';
                    } elseif ($product->stock < $item->quantity) {
                        $problems[] = "Stok {$product->name} tidak cukup (tersedia {$product->stock}, dipesan {$item->quantity}).";
                    }
                }

                if ($problems) {
                    throw ValidationException::withMessages([
                        'stock' => $problems,
                    ]);
                }

                foreach ($items as $item) {
                    $products->get($item->product_id)
                        ->decrement('stock', $item->quantity);
                }

                $locked->update([
                    'status' => 'completed',
                    'user_id' => $request->user()->id,
                    'sold_at' => now(),
                ]);
            });
        } catch (ValidationException $e) {
            return back()->with(
                'error',
                implode(' ', collect($e->errors())->flatten()->all())
            );
        }

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Pesanan dikonfirmasi. Stok produk sudah berkurang.');
    }

    public function reject(Request $request, Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale, $request) {
                $locked = Sale::lockForUpdate()->findOrFail($sale->id);

                if ($locked->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Pesanan ini sudah diproses sebelumnya.',
                    ]);
                }

                // Stok belum pernah dikurangi, jadi tidak ada yang dikembalikan.
                $locked->update([
                    'status' => 'cancelled',
                    'user_id' => $request->user()->id,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->with(
                'error',
                collect($e->errors())->flatten()->first()
            );
        }

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Pesanan ditolak. Stok tidak berubah.');
    }

    private function canView(User $user, Sale $sale): bool
    {
        return $user->isAdmin()
            || $sale->user_id === $user->id
            || $sale->status === 'pending';
    }

    private function generateNumber(): string
    {
        $prefix = 'SL-' . now()->format('Ymd') . '-';

        $last = Sale::where('sale_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('sale_number')
            ->value('sale_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function messages(): array
    {
        return [
            'customer_id.exists' => 'Pelanggan yang dipilih tidak tersedia.',
            'customer_name.max' => 'Nama pelanggan maksimal 100 karakter.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.min' => 'Tambahkan minimal satu produk.',
            'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'items.*.quantity.required' => 'Jumlah wajib diisi pada setiap baris.',
            'items.*.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.max' => 'Jumlah maksimal 100.000 per baris.',
        ];
    }
}