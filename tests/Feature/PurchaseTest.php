<?php

namespace Tests\Feature;

use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class PurchaseTest extends MedikaCareTestCase
{
    private function buy($user, $supplier, array $items)
    {
        return $this->actingAs($user)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => $items,
        ]);
    }

    public function test_purchase_adds_stock_by_quantity(): void
    {
        $staff = $this->staff();
        $product = $this->product(['stock' => 10]);

        $this->buy($staff, $this->supplier(), [
            ['product_id' => $product->id, 'quantity' => 15, 'unit_cost' => 1200],
        ])->assertRedirect();

        $this->assertSame(25, $product->fresh()->stock);
    }

    public function test_cancel_is_refused_when_stock_is_already_used(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->buy($this->staff(), $this->supplier(), [
            ['product_id' => $product->id, 'quantity' => 20, 'unit_cost' => 1000],
        ]);

        $purchase = Purchase::first();
        $product->update(['stock' => 5]);

        $this->actingAs($this->admin())
            ->post("/purchases/{$purchase->id}/cancel")
            ->assertSessionHas('error');

        $this->assertSame('completed', $purchase->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_staff_cannot_cancel_a_purchase(): void
    {
        $product = $this->product();

        $this->buy($this->staff(), $this->supplier(), [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 1000],
        ]);

        $this->actingAs($this->staff())
            ->post('/purchases/' . Purchase::first()->id . '/cancel')
            ->assertForbidden();
    }

    public function test_a_failing_step_rolls_back_every_change_in_the_transaction(): void
    {
        $product = $this->product(['stock' => 10]);

        try {
            DB::transaction(function () use ($product) {
                $product->increment('stock', 50);
                throw new \RuntimeException('gagal di tengah jalan');
            });
        } catch (\RuntimeException) {
            // Diharapkan: transaksi dibatalkan.
        }

        $this->assertSame(10, $product->fresh()->stock);
    }
}