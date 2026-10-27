<?php

namespace Tests\Feature;

use App\Models\Sale;

class SaleTest extends MedikaCareTestCase
{
    private function sell($user, array $items, array $extra = [])
    {
        return $this->actingAs($user)->post('/sales', array_merge([
            'payment_method' => 'tunai',
            'items' => $items,
        ], $extra));
    }

    public function test_sale_reduces_stock_and_ignores_price_from_browser(): void
    {
        $staff = $this->staff();
        $product = $this->product(['stock' => 20, 'selling_price' => 5000]);

        // Harga 1 dari browser harus diabaikan.
        $this->sell($staff, [
            ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1],
        ], ['customer_name' => 'Ibu Ani'])->assertRedirect();

        $this->assertSame(17, $product->fresh()->stock);

        $sale = Sale::first();
        $this->assertSame('completed', $sale->status);
        $this->assertSame($staff->id, $sale->user_id);
        $this->assertEquals(15000, $sale->total);
        $this->assertEquals(5000, $sale->items()->first()->unit_price);
        $this->assertStringStartsWith('SL-', $sale->sale_number);
    }

    public function test_sale_of_entire_stock_is_allowed(): void
    {
        $product = $this->product(['stock' => 4]);

        $this->sell($this->staff(), [
            ['product_id' => $product->id, 'quantity' => 4],
        ])->assertRedirect();

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_sale_above_stock_is_rejected_without_changing_data(): void
    {
        $product = $this->product(['stock' => 4]);

        $this->sell($this->staff(), [
            ['product_id' => $product->id, 'quantity' => 6],
        ])->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_one_failing_item_cancels_the_whole_sale(): void
    {
        $enough = $this->product(['stock' => 10]);
        $short = $this->product(['stock' => 1]);

        $this->sell($this->staff(), [
            ['product_id' => $enough->id, 'quantity' => 2],
            ['product_id' => $short->id, 'quantity' => 5],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $enough->fresh()->stock);
        $this->assertSame(1, $short->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $product = $this->product();

        $this->sell($this->staff(), [
            ['product_id' => $product->id, 'quantity' => 0],
        ])->assertSessionHasErrors('items.0.quantity');
    }

    public function test_admin_cancel_restores_stock(): void
    {
        $product = $this->product(['stock' => 20]);
        $this->sell($this->staff(), [['product_id' => $product->id, 'quantity' => 3]]);
        $sale = Sale::first();

        $this->actingAs($this->admin())
            ->post("/sales/{$sale->id}/cancel")
            ->assertRedirect();

        $this->assertSame(20, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);
    }

    public function test_sale_older_than_24_hours_cannot_be_cancelled(): void
    {
        $product = $this->product(['stock' => 20]);
        $this->sell($this->staff(), [['product_id' => $product->id, 'quantity' => 3]]);
        $sale = Sale::first();
        $sale->update(['sold_at' => now()->subDays(2)]);

        $this->actingAs($this->admin())
            ->post("/sales/{$sale->id}/cancel")
            ->assertSessionHas('error');

        $this->assertSame('completed', $sale->fresh()->status);
        $this->assertSame(17, $product->fresh()->stock);
    }

    public function test_staff_cannot_cancel_and_cannot_open_other_staff_sales(): void
    {
        $owner = $this->staff();
        $product = $this->product(['stock' => 20]);
        $this->sell($owner, [['product_id' => $product->id, 'quantity' => 1]]);
        $sale = Sale::first();

        $this->actingAs($owner)
            ->post("/sales/{$sale->id}/cancel")
            ->assertForbidden();

        $other = $this->staff();

        $this->actingAs($other)->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($other)->get("/sales/{$sale->id}/receipt")->assertForbidden();
        $this->actingAs($owner)->get("/sales/{$sale->id}/receipt")->assertOk();
    }
}