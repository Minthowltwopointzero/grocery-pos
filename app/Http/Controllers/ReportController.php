<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CreditPayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    private function dateRange(Request $request): array
    {
        $from = $request->get('date_from') ?: now()->startOfMonth()->format('Y-m-d');
        $to = $request->get('date_to') ?: now()->format('Y-m-d');

        return [$from, $to];
    }

    public function salesSummary(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Sale::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(total_amount) as total')
            ->selectRaw("SUM(CASE WHEN payment_type = 'cash' THEN total_amount ELSE 0 END) as cash_total")
            ->selectRaw("SUM(CASE WHEN payment_type = 'credit' THEN total_amount ELSE 0 END) as credit_total")
            ->groupBy('sale_date')
            ->orderByDesc('sale_date')
            ->get();

        $grandTotal = $rows->sum('total');
        $grandTransactions = $rows->sum('transactions');
        $grandCash = $rows->sum('cash_total');
        $grandCredit = $rows->sum('credit_total');

        $chartRows = $rows->sortBy('sale_date')->values();
        $chartLabels = $chartRows->pluck('sale_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M d'));
        $chartCash = $chartRows->pluck('cash_total');
        $chartCredit = $chartRows->pluck('credit_total');

        return view('reports.sales-summary', compact(
            'rows', 'from', 'to', 'grandTotal', 'grandTransactions', 'grandCash', 'grandCredit',
            'chartLabels', 'chartCash', 'chartCredit'
        ));
    }

    public function bestSelling(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereDate('sales.created_at', '>=', $from)
            ->whereDate('sales.created_at', '<=', $to)
            ->selectRaw('sale_items.product_name')
            ->selectRaw('SUM(sale_items.quantity) as total_qty')
            ->selectRaw('SUM(sale_items.subtotal) as total_revenue')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(30)
            ->get();

        $chartTop = $rows->take(15);
        $chartLabels = $chartTop->pluck('product_name');
        $chartQty = $chartTop->pluck('total_qty');

        return view('reports.best-selling', compact('rows', 'from', 'to', 'chartLabels', 'chartQty'));
    }

    public function inventory()
    {
        $products = Product::orderBy('name')->get();

        $totalValueCash = $products->sum(fn ($p) => $p->stock_quantity * $p->cash_price);
        $totalStockCount = $products->sum('stock_quantity');
        $lowStockCount = $products->filter(fn ($p) => $p->is_active && $p->stock_quantity <= 10)->count();

        $statusCounts = [
            'OK' => 0,
            'Low Stock' => 0,
            'Expired' => 0,
            'Expiring Soon' => 0,
            'Inactive' => 0,
        ];
        foreach ($products as $p) {
            if (! $p->is_active) {
                $statusCounts['Inactive']++;
            } elseif ($p->isExpired()) {
                $statusCounts['Expired']++;
            } elseif ($p->isExpiringSoon()) {
                $statusCounts['Expiring Soon']++;
            } elseif ($p->isLowStock()) {
                $statusCounts['Low Stock']++;
            } else {
                $statusCounts['OK']++;
            }
        }

        return view('reports.inventory', compact('products', 'totalValueCash', 'totalStockCount', 'lowStockCount', 'statusCounts'));
    }

    public function credit()
    {
        $customers = Customer::where('balance', '>', 0)->orderByDesc('balance')->get();
        $totalOutstanding = $customers->sum('balance');

        return view('reports.credit', compact('customers', 'totalOutstanding'));
    }

    public function paymentHistory(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = CreditPayment::with(['customer', 'receiver'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($customerId = $request->get('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $payments = $query->latest()->paginate(30)->withQueryString();

        $totalCollected = (clone $query)->sum('amount');
        $customers = Customer::orderBy('name')->get();

        // Chart 1: Daily collection trend (respects the same filters, but
        // not paginated - we want the full range for the chart)
        $dailyQuery = CreditPayment::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        if ($customerId) {
            $dailyQuery->where('customer_id', $customerId);
        }
        $dailyRows = $dailyQuery
            ->selectRaw('DATE(created_at) as pay_date')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('pay_date')
            ->orderBy('pay_date')
            ->get();
        $dailyLabels = $dailyRows->pluck('pay_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M d'));
        $dailyTotals = $dailyRows->pluck('total');

        // Chart 2: Top customers by payment amount within the range
        $topCustomersQuery = CreditPayment::join('customers', 'customers.id', '=', 'credit_payments.customer_id')
            ->whereDate('credit_payments.created_at', '>=', $from)
            ->whereDate('credit_payments.created_at', '<=', $to);
        if ($customerId) {
            $topCustomersQuery->where('credit_payments.customer_id', $customerId);
        }
        $topCustomerRows = $topCustomersQuery
            ->selectRaw('customers.name as customer_name')
            ->selectRaw('SUM(credit_payments.amount) as total_paid')
            ->groupBy('customers.name')
            ->havingRaw('SUM(credit_payments.amount) > 0')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get();
        $topCustomerLabels = $topCustomerRows->pluck('customer_name');
        $topCustomerTotals = $topCustomerRows->pluck('total_paid');

        return view('reports.payment-history', compact(
            'payments', 'from', 'to', 'totalCollected', 'customers',
            'dailyLabels', 'dailyTotals', 'topCustomerLabels', 'topCustomerTotals'
        ));
    }
}
