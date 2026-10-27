<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'in:rendah,habis'],
        ]);

        $products = Product::with('category')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('sku', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $id) => $query->where('category_id', $id)
            )
            ->when(
                ($filters['status'] ?? null) === 'rendah',
                fn ($query) => $query->lowStock()
            )
            ->when(
                ($filters['status'] ?? null) === 'habis',
                fn ($query) => $query->where('stock', 0)
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'countAll' => Product::count(),
            'countLow' => Product::lowStock()->count(),
            'countOut' => Product::where('stock', 0)->count(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalize($request);

        $data = $request->validate(
            $this->rules() + [
                'stock' => ['required', 'integer', 'min:0'],
            ],
            $this->messages()
        );

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('category')
            ->loadCount(['purchaseItems', 'saleItems']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->normalize($request);

        $data = $request->validate(
            $this->rules($product),
            $this->messages()
        );

        $product->update($data);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with(
                'success',
                'Produk dihapus. Riwayat transaksinya tetap tersimpan.'
            );
    }

    private function normalize(Request $request): void
    {
        $request->merge([
            'sku' => strtoupper(trim((string) $request->input('sku'))),
        ]);
    }

    private function rules(?Product $product = null): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
                'gte:purchase_price',
            ],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    private function messages(): array
    {
        return [
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 50 karakter.',
            'sku.unique' => 'SKU sudah dipakai produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
            'selling_price.gte' => 'Harga jual tidak boleh lebih rendah dari harga beli.',
            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.integer' => 'Stok minimum harus berupa bilangan bulat.',
            'minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
            'stock.required' => 'Stok awal wajib diisi.',
            'stock.integer' => 'Stok awal harus berupa bilangan bulat.',
            'stock.min' => 'Stok awal tidak boleh negatif.',
        ];
    }
}
