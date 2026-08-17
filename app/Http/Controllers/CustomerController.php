<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CreditPayment;
use App\Services\AuditLogger;
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

        $customer = Customer::create($validated);

        AuditLogger::log('customer_created', "Customer created: {$customer->name}" . ($customer->office ? " ({$customer->office})" : ''));

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

        $oldName = $customer->name;
        $customer->update($validated);

        AuditLogger::log('customer_updated', "Customer updated: {$oldName} → {$customer->name}");

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $balance = $customer->balance;

        $customer->delete();

        AuditLogger::log('customer_deleted', "Customer deleted: {$name} (balance at time of deletion: ₱" . number_format($balance, 2) . ")");

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    public function bulkUploadForm()
    {
        return view('customers.bulk-upload');
    }

    public function bulkUploadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_template.csv"',
        ];

        $columns = ['name', 'office'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Juan Dela Cruz', 'Accounting Office']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors(['csv_file' => 'Could not read the uploaded file.']);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'The file appears to be empty.']);
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        if (! in_array('name', $header, true)) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Missing required column: name. Please use the template.']);
        }

        $created = 0;
        $rowErrors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));
            $name = trim((string) ($data['name'] ?? ''));
            $office = trim((string) ($data['office'] ?? ''));

            if ($name === '') {
                $rowErrors[] = "Row {$rowNumber}: missing customer name, skipped.";
                continue;
            }

            Customer::create([
                'name' => $name,
                'office' => $office !== '' ? $office : null,
            ]);
            $created++;
        }

        fclose($handle);

        $summary = "Bulk upload complete: {$created} customer(s) added.";
        AuditLogger::log('customer_bulk_upload', $summary);

        return redirect()->route('customers.index')->with('success', $summary)
            ->with('bulkUploadErrors', $rowErrors);
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

            // If the customer is now fully paid off (balance reached zero),
            // mark all of their past credit sales as "paid" so Sales History
            // reflects reality — otherwise those old transactions would stay
            // stuck showing "Unpaid" forever even though the debt is settled.
            if ($customer->balance <= 0) {
                $customer->sales()
                    ->where('payment_type', 'credit')
                    ->where('status', '!=', 'paid')
                    ->update(['status' => 'paid']);
            }
        });

        AuditLogger::log(
            'credit_payment_recorded',
            "Credit payment of ₱" . number_format($validated['amount'], 2) . " recorded for {$customer->name} (remaining balance: ₱" . number_format($customer->balance, 2) . ")"
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Payment recorded successfully.');
    }
}
