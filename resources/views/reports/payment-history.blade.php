@extends('layouts.app')
@section('title', 'Payment History')
@section('page-title', 'Payment History')

@section('content')
<div class="mb-3 no-print">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    <button onclick="window.print()" class="btn btn-sm btn-dark float-end"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card p-3 mb-3 no-print">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label small mb-1">From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-1">To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-1">Customer</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-secondary">Apply</button>
        </div>
    </form>
</div>

<div class="card p-3 mb-3">
    <div class="text-muted small">Total Collected ({{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }})</div>
    <h3 class="text-success">₱{{ number_format($totalCollected, 2) }}</h3>
</div>

<div class="card p-3">
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Customer</th><th>Amount</th><th>Balance After</th><th>Received By</th><th>Notes</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td class="text-nowrap">{{ $payment->created_at->format('M d, Y g:ia') }}</td>
                <td>
                    @if($payment->customer)
                        <a href="{{ route('customers.show', $payment->customer) }}">{{ $payment->customer->name }}</a>
                    @else
                        <span class="text-muted">(deleted customer)</span>
                    @endif
                </td>
                <td class="text-success fw-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                <td>₱{{ number_format($payment->balance_after, 2) }}</td>
                <td>{{ $payment->receiver->name ?? '-' }}</td>
                <td class="small">{{ $payment->notes ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No payments recorded in this date range.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
