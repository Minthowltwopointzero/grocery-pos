<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->get();

        return view('pos.index', compact('customers'));
    }

    /**
     * Process the checkout submitted from the POS screen.
     * Expects JSON: payment_type, customer_id (if credit), amount_paid (if cash),
     * items: [{ product_id, quantity }]
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'in:cash,credit'],
            'customer_id' => ['required_if:payment_type,credit', 'nullable', 'exists:customers,id'],
            'amount_paid' => ['required_if:payment_type,cash', 'nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $request) {
                $priceField = $validated['payment_type'] === 'cash' ? 'cash_price' : 'credit_price';
                $total = 0;
                $lineItems = [];

                foreach ($validated['items'] as $item) {
                    // Lock the product row to prevent overselling under concurrent checkouts
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    if (! $product || ! $product->is_active) {
                        throw ValidationException::withMessages(['items' => "Product not found or inactive."]);
                    }

                    if ($product->stock_quantity < $item['quantity']) {
                        throw ValidationException::withMessages(['items' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"]);
                    }

                    $unitPrice = $product->{$priceField};
                    $subtotal = $unitPrice * $item['quantity'];
                    $total += $subtotal;

                    $lineItems[] = [
                        'product' => $product,
                        'unit_price' => $unitPrice,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                    ];
                }

                $amountPaid = 0;
                $change = 0;
                $status = 'paid';
                $customerId = null;

                if ($validated['payment_type'] === 'cash') {
                    $amountPaid = $validated['amount_paid'];
                    if ($amountPaid < $total) {
                        throw ValidationException::withMessages(['amount_paid' => 'Payment is less than the total amount due.']);
                    }
                    $change = round($amountPaid - $total, 2);
                    $status = 'paid';
                } else {
                    $customerId = $validated['customer_id'];
                    $status = 'unpaid';
                }

                $sale = Sale::create([
                    'invoice_no' => Sale::generateInvoiceNo(),
                    'user_id' => $request->user()->id,
                    'customer_id' => $customerId,
                    'payment_type' => $validated['payment_type'],
                    'total_amount' => $total,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $change,
                    'status' => $status,
                ]);

                foreach ($lineItems as $line) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'subtotal' => $line['subtotal'],
                    ]);

                    $line['product']->decrement('stock_quantity', $line['quantity']);
                }

                if ($validated['payment_type'] === 'credit') {
                    $customer = Customer::where('id', $customerId)->lockForUpdate()->first();
                    $customer->balance += $total;
                    $customer->save();
                }

                AuditLogger::log(
                    'sale_completed',
                    "Completed {$validated['payment_type']} sale {$sale->invoice_no} with a total of ₱" . number_format($total, 2)
                );

                return $sale;
            });

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'receipt_url' => route('sales.receipt', $sale),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
