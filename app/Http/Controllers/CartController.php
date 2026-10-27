<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->cart($request);

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        // produk yang sudah dihapus apotek otomatis keluar dari keranjang
        $cart = array_intersect_key($cart, $products->all());
        $request->session()->put('cart', $cart);

        $lines = [];
        $total = 0;

        foreach ($cart as $id => $qty) {
            $product = $products->get($id);
            $subtotal = $qty * (float) $product->selling_price;

            $lines[] = [
                'product' => $product,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'over' => $qty > $product->stock,
            ];

            $total += $subtotal;
        }

        return view('shop.cart', compact('lines', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        $product = Product::findOrFail($data['product_id']);
        $cart = $this->cart($request);
        $inCart = $cart[$product->id] ?? 0;
        $newQty = $inCart + (int) $data['quantity'];

        if ($product->stock < 1) {
            return back()->with('error', "{$product->name} sedang habis.");
        }

        if ($newQty > $product->stock) {
            return back()->with('error',
                "Stok {$product->name} hanya {$product->stock}. Di keranjang Anda sudah ada {$inCart}.");
        }

        $cart[$product->id] = $newQty;
        $request->session()->put('cart', $cart);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        $cart = $this->cart($request);
        abort_unless(isset($cart[$id]), 404);

        $product = Product::find($id);

        if (! $product) {
            unset($cart[$id]);
            $request->session()->put('cart', $cart);

            return back()->with('error', 'Produk ini sudah tidak tersedia.');
        }

        if ($data['quantity'] > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya {$product->stock}.");
        }

        $cart[$id] = (int) $data['quantity'];
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function remove(Request $request, int $id)
    {
        $cart = $this->cart($request);
        unset($cart[$id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:tunai,transfer'],
        ], $this->messages());

        $cart = $this->cart($request);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $user = $request->user();

        $sale = DB::transaction(function () use ($cart, $data, $user) {
            // kunci produk berurutan, lalu cek stok
            $ids = array_map('intval', array_keys($cart));

            $products = Product::whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $problems = [];

            foreach ($cart as $id => $qty) {
                $product = $products->get((int) $id);

                if (! $product) {
                    $problems[] = 'Ada produk di keranjang yang sudah tidak tersedia.';
                } elseif ($product->stock < $qty) {
                    $problems[] = "Stok {$product->name} hanya {$product->stock}, sedangkan di keranjang {$qty}.";
                }
            }

            if ($problems) {
                throw ValidationException::withMessages(['cart' => $problems]);
            }

            // pesanan menunggu: stok BELUM berkurang, kasir belum ada
            $sale = Sale::create([
                'customer_id' => $user->id,
                'user_id' => null,
                'customer_name' => $user->name,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'sold_at' => null,
            ]);

            $total = 0;

            foreach ($cart as $id => $qty) {
                $product = $products->get((int) $id);
                $price = (float) $product->selling_price; // harga dikunci saat memesan
                $subtotal = round($qty * $price, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        $request->session()->forget('cart');

        return redirect()->route('orders.show', $sale)
            ->with('success', "Pesanan {$sale->sale_number} berhasil dibuat. Menunggu konfirmasi apotek.");
    }

    private function cart(Request $request): array
    {
        return $request->session()->get('cart', []);
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
            'product_id.required' => 'Pilih produk terlebih dahulu.',
            'product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah maksimal 100 per produk.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}