<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statistics()
    {
        $today = Carbon::today();

        // Total Produk
        $totalProducts = Product::count();

        // Produk Terjual Hari Ini
        $soldToday = Sale::whereDate('sale_date', $today)->sum('quantity');

        // Pendapatan Hari Ini
        $revenueToday = Sale::whereDate('sale_date', $today)->sum('total');

        // Statistik per Hari (7 Hari Terakhir)
        $salesByDay = Sale::select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(total) as total_revenue')
        )
            ->whereBetween('sale_date', [$today->copy()->subDays(6), $today])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return response()->json([
            'total_products' => $totalProducts,
            'sold_today' => $soldToday,
            'revenue_today' => $revenueToday,
            'sales_chart' => $salesByDay,
        ]);
    }
}
