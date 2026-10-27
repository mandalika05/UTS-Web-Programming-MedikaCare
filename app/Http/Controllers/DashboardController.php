<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isCustomer()) {
            return redirect()->route('shop.index');
        }

        $isAdmin = $user->isAdmin();

        // Admin melihat semua penjualan, Staff hanya miliknya
        $sales = Sale::where('status', 'completed')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));

        $todaySales = (clone $sales)->where('sold_at', '>=', now()->startOfDay());
        $monthSales = (clone $sales)->where('sold_at', '>=', now()->startOfMonth());

        $perDay = (clone $sales)
            ->where('sold_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(sold_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $salesLabels = [];
        $salesValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesLabels[] = $date->locale('id')->translatedFormat('D, d/m');
            $salesValues[] = (float) ($perDay[$date->toDateString()] ?? 0);
        }

        $totalProducts = Product::count();
        $low = Product::lowStock()->count();
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };

        $byCategory = Category::withSum('products as total_stock', 'stock')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'isAdmin' => $isAdmin,
            'greeting' => $greeting,
            'today' => now()->locale('id')->translatedFormat('l, d F Y'),
            'salesToday' => (clone $todaySales)->sum('total'),
            'transactionsToday' => (clone $todaySales)->count(),
            'salesMonth' => (clone $monthSales)->sum('total'),
            'transactionsMonth' => (clone $monthSales)->count(),
            'totalProducts' => $totalProducts,
            'lowStock' => $low,
            'totalCategories' => Category::count(),
            'stockHealth' => $totalProducts > 0 ? round(($totalProducts - $low) / $totalProducts * 100) : 100,
            'salesLabels' => $salesLabels,
            'salesValues' => $salesValues,
            'chartLabels' => $byCategory->pluck('name'),
            'chartValues' => $byCategory->pluck('total_stock')->map(fn ($v) => (int) $v),
            'lowStockItems' => Product::lowStock()
                ->with('category')
                ->orderBy('stock')
                ->limit(6)
                ->get(),
        ]);
    }
}
