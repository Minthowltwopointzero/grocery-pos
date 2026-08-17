<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function buildDashboardData(): array
    {
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $todayTransactions = Sale::whereDate('created_at', today())->count();
        $lowStockCount = Product::where('is_active', true)->whereColumn('stock_quantity', '<=', DB::raw(10))->count();
        $totalCreditOutstanding = Customer::sum('balance');

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

        return compact(
            'todaySales', 'todayTransactions', 'lowStockCount',
            'totalCreditOutstanding', 'lowStockProducts', 'recentSales',
            'expiringSoonProducts', 'expiredProducts'
        );
    }

    public function index()
    {
        return view('dashboard', $this->buildDashboardData());
    }

    // AJAX endpoint polled every few seconds by the dashboard page to keep
    // stats/tables fresh without a full page reload (no flicker, no lost
    // scroll position or in-progress typing).
    public function data()
    {
        $d = $this->buildDashboardData();

        return response()->json([
            'today_sales' => number_format($d['todaySales'], 2),
            'today_transactions' => $d['todayTransactions'],
            'low_stock_count' => $d['lowStockCount'],
            'total_credit_outstanding' => number_format($d['totalCreditOutstanding'], 2),
            'recent_sales' => $d['recentSales']->map(fn ($s) => [
                'invoice_no' => $s->invoice_no,
                'cashier' => $s->user->name,
                'payment_type' => $s->payment_type,
                'total' => number_format($s->total_amount, 2),
                'receipt_url' => route('sales.receipt', $s),
            ]),
            'low_stock_products' => $d['lowStockProducts']->map(fn ($p) => [
                'name' => $p->name,
                'stock_quantity' => $p->stock_quantity,
            ]),
            'expired_products' => $d['expiredProducts']->map(fn ($p) => [
                'name' => $p->name,
                'expiration_date' => $p->expiration_date->format('M d, Y'),
            ]),
            'expiring_soon_products' => $d['expiringSoonProducts']->map(fn ($p) => [
                'name' => $p->name,
                'expiration_date' => $p->expiration_date->format('M d, Y'),
            ]),
        ]);
    }
}
