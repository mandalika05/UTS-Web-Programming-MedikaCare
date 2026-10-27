<?php

namespace Tests\Feature;

use App\Models\Category;

class ProductTest extends MedikaCareTestCase
{
    private function payload(array $override = []): array
    {
        $category = Category::firstOrCreate(['name' => 'Obat Bebas']);

        return array_merge([
            'sku' => 'NEW-001',
            'name' => 'Produk Baru',
            'category_id' => $category->id,
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'minimum_stock' => 5,
            'stock' => 7,
        ], $override);
    }

    public function test_admin_can_create_a_product(): void
    {
        $this->actingAs($this->admin())
            ->post('/products', $this->payload())
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'NEW-001', 'stock' => 7]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $this->product(['sku' => 'OBT-900']);

        $this->actingAs($this->admin())
            ->post('/products', $this->payload(['sku' => 'obt-900']))
            ->assertSessionHasErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/products', $this->payload(['purchase_price' => -5]))
            ->assertSessionHasErrors('purchase_price');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_selling_price_cannot_be_lower_than_purchase_price(): void
    {
        $this->actingAs($this->admin())
            ->post('/products', $this->payload([
                'purchase_price' => 5000,
                'selling_price' => 1000,
            ]))
            ->assertSessionHasErrors('selling_price');
    }

    public function test_search_and_stock_filter_show_matching_products(): void
    {
        $this->product([
            'name' => 'Vitamin Uji',
            'sku' => 'VIT-900',
            'stock' => 2,
            'minimum_stock' => 5,
        ]);

        $this->product([
            'name' => 'Paracetamol Uji',
            'sku' => 'PCT-900',
            'stock' => 50,
            'minimum_stock' => 5,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)->get('/products?q=vitamin')
            ->assertSee('Vitamin Uji')
            ->assertDontSee('Paracetamol Uji');

        $this->actingAs($admin)->get('/products?q=PCT-900')
            ->assertSee('Paracetamol Uji')
            ->assertDontSee('Vitamin Uji');

        $this->actingAs($admin)->get('/products?status=rendah')
            ->assertSee('Vitamin Uji')
            ->assertDontSee('Paracetamol Uji');
    }

    public function test_pagination_shows_ten_per_page(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->product();
        }

        $admin = $this->admin();

        $this->actingAs($admin)->get('/products')
            ->assertViewHas('products', fn ($p) => $p->count() === 10 && $p->total() === 12);

        $this->actingAs($admin)->get('/products?page=2')
            ->assertViewHas('products', fn ($p) => $p->count() === 2);
    }

    public function test_deleted_product_is_soft_deleted(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())
            ->delete("/products/{$product->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}