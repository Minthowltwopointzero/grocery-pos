@extends('layouts.app')
@section('title', 'Customer Ledger')
@section('page-title', 'Credit Ledger — ' . $customer->name)

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <div class="text-muted small">Customer</div>
            <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
            <div class="text-muted mb-3">{{ $customer->office ?? '-' }}</div>
            <div class="text-muted small">Current Balance</div>
            <h3 class="text-{{ $customer->balance > 0 ? 'danger' : 'success' }}">₱{{ number_format($customer->balance, 2) }}</h3>
        </div>

        <div class="card p-3">
            <h6 class="fw-bold mb-3">Add Payment</h6>
            <form method="POST" action="{{ route('customers.payments.store', $customer) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Amount</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $customer->balance }}" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Notes (optional)</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <button class="btn btn-dark w-100" {{ $customer->balance <= 0 ? 'disabled' : '' }}>Record Payment</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-3">Credit Purchases</h6>
            <table class="table table-sm">
                <thead><tr><th>Invoice</th><th>Date</th><th>Items</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td><a href="{{ route('sales.receipt', $sale) }}">{{ $sale->invoice_no }}</a></td>
                        <td>{{ $sale->created_at->format('M d, Y g:ia') }}</td>
                        <td>{{ $sale->items->count() }} item(s)</td>
                        <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No credit purchases yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card p-3">
            <h6 class="fw-bold mb-3">Payment History</h6>
            <table class="table table-sm">
                <thead><tr><th>Date</th><th>Amount</th><th>Balance After</th><th>Received By</th><th>Notes</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('M d, Y g:ia') }}</td>
                        <td class="text-success">₱{{ number_format($payment->amount, 2) }}</td>
                        <td>₱{{ number_format($payment->balance_after, 2) }}</td>
                        <td>{{ $payment->receiver->name }}</td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No payments recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
