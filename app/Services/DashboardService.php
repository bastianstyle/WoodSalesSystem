<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function overview(): array
    {
        $today = now()->startOfDay();

        $sales = Sale::query()->where('status', 'issued');

        $trend = collect(range(6, 0))->map(function (int $days) use ($sales) {
            $date = now()->subDays($days);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('d M'),
                'total' => (float) (clone $sales)->whereDate('issued_at', $date)->sum('grand_total'),
            ];
        });

        return [
            'todayRevenue' => (float) (clone $sales)->where('issued_at', '>=', $today)->sum('grand_total'),
            'activeOrders' => (int) (clone $sales)->whereIn('status', ['issued', 'processing'])->count(),
            'pendingDispatch' => 0,
            'outstandingReceivables' => (float) (clone $sales)->whereColumn('amount_paid', '<', 'grand_total')->sum(DB::raw('grand_total - amount_paid')),
            'totalProducts' => Product::where('is_active', true)->count(),
            'totalStock' => (float) Product::where('is_active', true)->sum('stock'),
            'trend' => $trend,
            'recentSales' => Sale::with(['customer', 'details'])->latest('issued_at')->limit(5)->get(),
            'lowStockProducts' => Product::where('is_active', true)->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->get(),
            'lastUpdated' => now(),
        ];
    }
}
