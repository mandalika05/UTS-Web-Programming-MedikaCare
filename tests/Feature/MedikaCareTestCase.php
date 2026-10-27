<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class MedikaCareTestCase extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    protected function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    protected function product(array $attributes = []): Product
    {
        static $n = 0;
        $n++;

        $category = Category::firstOrCreate(['name' => 'Obat Bebas']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'TST-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
            'name' => 'Produk Uji ' . $n,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 5,
        ], $attributes));
    }

    protected function supplier(): Supplier
    {
        return Supplier::create(['name' => 'Supplier Uji']);
    }
}
