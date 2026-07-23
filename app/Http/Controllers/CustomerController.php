<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('office', 'like', "%{$search}%");
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

   public function create()
{
      return view('customers.create');
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    // Credit tracking page: balance + full history of sales & payments
    public function show(Customer $customer)
    {
        $sales = $customer->sales()->with('items')->where('payment_type', 'credit')->latest()->get();
        $payments = $customer->payments()->with('receiver')->latest()->get();

        return view('customers.show', compact('customer', 'sales', 'payments'));
    }

    public function addPayment(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['amount'] > $customer->balance) {
            return back()->withErrors(['amount' => 'Payment cannot exceed the current balance of ' . number_format($customer->balance, 2)]);
        }

        DB::transaction(function () use ($customer, $validated, $request) {
            $customer->balance -= $validated['amount'];
            $customer->save();

            CreditPayment::create([
                'customer_id' => $customer->id,
                'received_by' => $request->user()->id,
                'amount' => $validated['amount'],
                'balance_after' => $customer->balance,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Payment recorded successfully.');
    }
}
