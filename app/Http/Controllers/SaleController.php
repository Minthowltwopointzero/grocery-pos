<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer']);

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($paymentType = $request->get('payment_type')) {
            $query->where('payment_type', $paymentType);
        }

        if ($search = $request->get('search')) {
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        $sales = $query->latest()->paginate(20)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    // Cashier-facing: only their own transactions, so they can look up and
    // reprint a receipt for a customer without seeing store-wide sales data.
    public function myTransactions(Request $request)
    {
        $query = Sale::with('customer')->where('user_id', $request->user()->id);

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($search = $request->get('search')) {
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        $sales = $query->latest()->paginate(20)->withQueryString();

        return view('sales.my-transactions', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'user', 'customer']);

        return view('sales.receipt', compact('sale'));
    }
}
