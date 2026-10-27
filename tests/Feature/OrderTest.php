<?php

namespace Tests\Feature;

use App\Models\Sale;

class OrderTest extends MedikaCareTestCase
{
    private function order($customer, $product, int $qty, string $payment = 'tunai')
    {
        return $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => $qty]])
            ->post('/keranjang/pesan', ['payment_method' => $payment]);
    }

    public function test_adding_to_cart_respects_stock(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock' => 3]);

        $this->actingAs($customer)
            ->post('/keranjang', ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->actingAs($customer)
            ->post('/keranjang', ['product_id' => $product->id, 'quantity' => 5])
            ->assertSessionHas('error');
    }

    public function test_customer_order_is_pending_and_does_not_change_stock(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock' => 10, 'selling_price' => 3000]);

        $this->order($customer, $product, 4, 'transfer')->assertRedirect();

        $sale = Sale::first();
        $this->assertSame('pending', $sale->status);
        $this->assertSame($customer->id, $sale->customer_id);
        $this->assertNull($sale->user_id);
        $this->assertSame('transfer', $sale->payment_method);
        $this->assertEquals(12000, $sale->total);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_staff_confirmation_reduces_stock_and_completes_the_order(): void
    {
        $product = $this->product(['stock' => 10, 'selling_price' => 3000]);
        $this->order($this->customer(), $product, 4);
        $sale = Sale::first();
        $staff = $this->staff();

        $this->actingAs($staff)
            ->post("/sales/{$sale->id}/confirm")
            ->assertRedirect();

        $sale->refresh();
        $this->assertSame('completed', $sale->status);
        $this->assertSame($staff->id, $sale->user_id);
        $this->assertNotNull($sale->sold_at);
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_confirmation_is_refused_when_stock_is_no_longer_enough(): void
    {
        $product = $this->product(['stock' => 10]);
        $this->order($this->customer(), $product, 4);
        $sale = Sale::first();

        $product->update(['stock' => 2]); // Terjual lewat kasir lebih dulu.

        $this->actingAs($this->staff())
            ->post("/sales/{$sale->id}/confirm")
            ->assertSessionHas('error');

        $this->assertSame('pending', $sale->fresh()->status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_an_order_cannot_be_confirmed_twice(): void
    {
        $product = $this->product(['stock' => 10]);
        $this->order($this->customer(), $product, 4);
        $sale = Sale::first();
        $staff = $this->staff();

        $this->actingAs($staff)->post("/sales/{$sale->id}/confirm");
        $this->actingAs($staff)
            ->post("/sales/{$sale->id}/confirm")
            ->assertSessionHas('error');

        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_staff_can_reject_a_pending_order_without_touching_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $this->order($this->customer(), $product, 4);
        $sale = Sale::first();

        $this->actingAs($this->staff())
            ->post("/sales/{$sale->id}/reject")
            ->assertRedirect();

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_customer_can_cancel_own_pending_order_but_not_after_confirmation(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock' => 10]);

        $this->order($customer, $product, 2);
        $first = Sale::first();

        $this->actingAs($customer)
            ->post("/pesanan/{$first->id}/batal")
            ->assertRedirect();

        $this->assertSame('cancelled', $first->fresh()->status);

        $this->order($customer, $product, 2);
        $second = Sale::orderByDesc('id')->first();

        $this->actingAs($this->staff())
            ->post("/sales/{$second->id}/confirm");

        $this->actingAs($customer)
            ->post("/pesanan/{$second->id}/batal")
            ->assertSessionHas('error');

        $this->assertSame('completed', $second->fresh()->status);
    }

    public function test_customer_cannot_open_someone_elses_order(): void
    {
        $product = $this->product();
        $this->order($this->customer(), $product, 1);
        $sale = Sale::first();

        $this->actingAs($this->customer())
            ->get("/pesanan/{$sale->id}")
            ->assertForbidden();

        $this->actingAs($this->customer())
            ->post("/pesanan/{$sale->id}/batal")
            ->assertForbidden();
    }

    public function test_catalog_shows_selling_price_but_never_purchase_price(): void
    {
        $this->product([
            'name' => 'Produk Katalog',
            'purchase_price' => 7777,
            'selling_price' => 12345,
        ]);

        $this->get('/katalog')
            ->assertSee('Produk Katalog')
            ->assertSee('12.345')
            ->assertDontSee('7.777');
    }
}