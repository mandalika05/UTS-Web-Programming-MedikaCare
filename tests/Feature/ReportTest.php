<?php

namespace Tests\Feature;

use App\Models\Sale;

class ReportTest extends MedikaCareTestCase
{
    private function sale($staff, int $total, string $status, string $when, string $number): Sale
    {
        return Sale::create([
            'user_id' => $staff->id,
            'sale_number' => $number,
            'total' => $total,
            'status' => $status,
            'payment_method' => 'tunai',
            'sold_at' => $when,
        ]);
    }

    public function test_only_staff_and_customers_are_blocked_from_reports(): void
    {
        $this->actingAs($this->staff())->get('/laporan/penjualan')->assertForbidden();
        $this->actingAs($this->customer())->get('/laporan/penjualan')->assertForbidden();
    }

    public function test_monthly_report_counts_only_completed_sales_inside_the_month(): void
    {
        $staff = $this->staff();

        $this->sale($staff, 10000, 'completed', '2026-10-10 10:00:00', 'SL-T-0001');
        $this->sale($staff, 5000, 'completed', '2026-10-20 15:00:00', 'SL-T-0002');
        $this->sale($staff, 77777, 'completed', '2026-09-30 10:00:00', 'SL-T-0003');
        $this->sale($staff, 99999, 'cancelled', '2026-10-12 10:00:00', 'SL-T-0004');

        $this->actingAs($this->admin())
            ->get('/laporan/penjualan?period=bulanan&date=2026-10-15')
            ->assertOk()
            ->assertSee('15.000')
            ->assertDontSee('77.777')
            ->assertDontSee('99.999');
    }

    public function test_report_can_be_filtered_per_staff(): void
    {
        $first = $this->staff();
        $second = $this->staff();

        $this->sale($first, 10000, 'completed', '2026-10-10 10:00:00', 'SL-T-0011');
        $this->sale($second, 3333, 'completed', '2026-10-11 10:00:00', 'SL-T-0012');

        $this->actingAs($this->admin())
            ->get("/laporan/penjualan?period=bulanan&date=2026-10-15&staff_id={$first->id}")
            ->assertOk()
            ->assertSee('10.000')
            ->assertDontSee('3.333');
    }
}