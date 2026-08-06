<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $todayTransactions = Sale::whereDate('created_at', today())->count();
        $lowStockCount = Product::where('is_active', true)->whereColumn('stock_quantity', '<=', DB::raw(10))->count();
        $totalCreditOutstanding = Customer::sum('balance');

        // Show ALL low-stock products (no artificial cap) so the count above
        // always matches what's listed below. The list is wrapped in a
        // scrollable box in the view if it gets long.
        $lowStockProducts = Product::where('is_active', true)->where('stock_quantity', '<=', 10)->orderBy('stock_quantity')->get();

        $recentSales = Sale::with(['user', 'customer'])->latest()->limit(8)->get();

        $expiringSoonProducts = Product::where('is_active', true)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', today())
            ->whereDate('expiration_date', '<=', now()->addDays(30))
            ->orderBy('expiration_date')
            ->get();

        $expiredProducts = Product::where('is_active', true)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', today())
            ->orderBy('expiration_date')
            ->get();

        return view('dashboard', compact(
            'todaySales', 'todayTransactions', 'lowStockCount',
            'totalCreditOutstanding', 'lowStockProducts', 'recentSales',
            'expiringSoonProducts', 'expiredProducts'
        ));
    }
}
