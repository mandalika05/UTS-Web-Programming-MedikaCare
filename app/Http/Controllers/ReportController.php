<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $report = $this->build($request);

        $transactions = (clone $report['base'])
            ->with('user')
            ->orderByDesc('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('reports.sales', $report['data'] + [
            'transactions' => $transactions,
        ]);
    }

    public function printable(Request $request)
    {
        $report = $this->build($request);

        $transactions = (clone $report['base'])
            ->with('user')
            ->orderBy('sold_at')
            ->limit(500)
            ->get();

        return view('reports.print', $report['data'] + [
            'transactions' => $transactions,
        ]);
    }

    private function build(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:harian,mingguan,bulanan'],
            'date' => ['nullable', 'date'],
            'staff_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->whereIn('role', ['admin', 'staff']),
            ],
        ]);

        $period = $filters['period'] ?? 'harian';
        $date = Carbon::parse(
            $filters['date'] ?? now()->toDateString()
        );
        $staffId = $filters['staff_id'] ?? null;

        [$start, $end] = match ($period) {
            'mingguan' => [
                $date->copy()->startOfWeek(Carbon::MONDAY),
                $date->copy()->endOfWeek(Carbon::SUNDAY),
            ],
            'bulanan' => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            ],
            default => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ],
        };

        $label = match ($period) {
            'mingguan' =>
                $start->copy()->locale('id')->translatedFormat('d M Y')
                . ' - '
                . $end->copy()->locale('id')->translatedFormat('d M Y'),

            'bulanan' =>
                $start->copy()->locale('id')->translatedFormat('F Y'),

            default =>
                $start->copy()->locale('id')->translatedFormat('l, d F Y'),
        };

        $base = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$start, $end])
            ->when(
                $staffId,
                fn ($query) => $query->where('user_id', $staffId)
            );

        $items = fn () => SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$start, $end])
            ->when(
                $staffId,
                fn ($query) => $query->where('sales.user_id', $staffId)
            );

        $count = (clone $base)->count();
        $total = (clone $base)->sum('total');
        $average = $count > 0 ? $total / $count : 0;

        $units = (clone $items())->sum('sale_items.quantity');

        // Daftar pilihan kasir untuk filter laporan.
        $staffOptions = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('name')
            ->get();

        // Dipertahankan untuk tampilan lain yang memakai variabel staff.
        $staff = $staffOptions;

        $staffLabel = $staffId
            ? (User::query()->find($staffId)?->name ?? 'Kasir')
            : 'Semua kasir';

        $perStaff = (clone $base)
            ->selectRaw(
                'user_id, COUNT(*) as trx, SUM(total) as revenue'
            )
            ->groupBy('user_id')
            ->orderByDesc('revenue')
            ->get();

        $names = User::query()
            ->whereIn(
                'id',
                $perStaff->pluck('user_id')->filter()->unique()
            )
            ->pluck('name', 'id');

        $topProducts = $items()
            ->join(
                'products',
                'products.id',
                '=',
                'sale_items.product_id'
            )
            ->selectRaw(
                'products.name, products.sku, '
                . 'SUM(sale_items.quantity) as qty, '
                . 'SUM(sale_items.subtotal) as revenue'
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku'
            )
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Data transaksi per hari untuk grafik.
        $chartQuery = (clone $base)
            ->selectRaw(
                'DATE(sold_at) as day, '
                . 'COUNT(*) as trx, SUM(total) as revenue'
            )
            ->groupByRaw('DATE(sold_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chart = collect();
        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();

        while ($cursor->lte($lastDay)) {
            $day = $cursor->toDateString();
            $row = $chartQuery->get($day);

            $chart->push([
                'date' => $day,
                'label' => $cursor->copy()
                    ->locale('id')
                    ->translatedFormat('d M'),
                'trx' => (int) ($row->trx ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
            ]);

            $cursor->addDay();
        }

        // Nama variabel disesuaikan dengan sales.blade.php.
        $chartLabels = $chart->pluck('label')->all();
        $chartValues = $chart->pluck('revenue')->all();

        return [
            'base' => $base,
            'data' => [
                'period' => $period,
                'date' => $date->toDateString(),
                'staffId' => $staffId,
                'staffOptions' => $staffOptions,
                'staff' => $staff,
                'staffLabel' => $staffLabel,
                'start' => $start,
                'end' => $end,
                'label' => $label,
                'count' => $count,
                'total' => $total,
                'average' => $average,
                'units' => $units,
                'perStaff' => $perStaff,
                'names' => $names,
                'topProducts' => $topProducts,
                'chart' => $chart,
                'chartLabels' => $chartLabels,
                'chartValues' => $chartValues,
            ],
        ];
    }
}