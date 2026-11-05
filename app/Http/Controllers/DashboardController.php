<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $timeFilter = $request->get('period', 'today');
        $user = Auth::user();
        $isKasir = $user ? $user->isKasir() : false;

        // Metric Calculations
        $totalStockUnits = Product::sum('stock');
        
        // Only calculate inventory value (modal aset) for Admin
        $inventoryValue = $isKasir 
            ? 0 
            : (Product::selectRaw('SUM(stock * cost_price) as total_val')->value('total_val') ?? 0);
        
        $criticalProductsCount = Product::whereRaw('stock <= min_stock')->count();
        
        // Determine date range based on period filter (today, week, month, all)
        $startDate = null;
        $endDate = null;
        $periodLabel = 'Hari Ini';

        if ($timeFilter === 'today') {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $periodLabel = 'Hari Ini';
        } elseif ($timeFilter === 'week') {
            $startDate = Carbon::now()->startOfWeek()->startOfDay();
            $endDate = Carbon::now()->endOfWeek()->endOfDay();
            $periodLabel = 'Pekan Ini';
        } elseif ($timeFilter === 'month') {
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->endOfMonth()->endOfDay();
            $periodLabel = 'Bulan Ini';
        } elseif ($timeFilter === 'all') {
            $startDate = null;
            $endDate = null;
            $periodLabel = 'Semua';
        } else {
            $timeFilter = 'today';
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $periodLabel = 'Hari Ini';
        }

        // Real sales data from transactions according to period
        $salesQuery = Transaction::where('status', 'success');
        if ($startDate && $endDate) {
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $salesValue = (float) $salesQuery->sum('total_amount');
        $transactionsCount = $salesQuery->count();

        // Calculate expenses for the period (Pembelian Suplier)
        $expenseQuery = StockMovement::where('type', 'in')->with('product');
        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        $expenseMovements = $expenseQuery->get();
        $expenseValue = (float) $expenseMovements->sum(function($m) {
            return (float) ($m->quantity * ($m->product->cost_price ?? 0));
        });
        $netProfit = $salesValue - $expenseValue;

        // Barang Prioritas Restock (stok <= min_stock)
        $restockProducts = Product::whereRaw('stock <= min_stock')
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        $totalCriticalCount = $criticalProductsCount;

        return view('dashboard', [
            'timeFilter'             => $timeFilter,
            'periodLabel'            => $periodLabel,
            'totalStockUnits'        => $totalStockUnits,
            'inventoryValue'         => $inventoryValue,
            'criticalProductsCount'  => $criticalProductsCount,
            'salesValue'             => $salesValue,
            'transactionsCount'      => $transactionsCount,
            'todaySalesValue'        => $salesValue, // backward compatibility
            'todayTransactionsCount' => $transactionsCount, // backward compatibility
            'expenseValue'           => $expenseValue,
            'netProfit'              => $netProfit,
            'restockProducts'        => $restockProducts,
            'totalCriticalCount'     => $totalCriticalCount,
            'isKasir'                => $isKasir,
        ]);
    }
}
