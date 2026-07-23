@extends('layouts.app')
@section('title', 'Sales History')
@section('page-title', 'Sales History')

@section('content')
<div class="card p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
        </div>
        <div class="col-auto">
            <select name="payment_type" class="form-select form-select-sm">
                <option value="">All Payment Types</option>
                <option value="cash" {{ request('payment_type')==='cash'?'selected':'' }}>Cash</option>
                <option value="credit" {{ request('payment_type')==='credit'?'selected':'' }}>Credit</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice #" value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Invoice</th><th>Date</th><th>Cashier</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ $sale->created_at->format('M d, Y g:ia') }}</td>
                <td>{{ $sale->user->name }}</td>
                <td>{{ $sale->customer->name ?? '-' }}</td>
                <td><span class="badge bg-{{ $sale->payment_type === 'cash' ? 'success' : 'warning' }}">{{ ucfirst($sale->payment_type) }}</span></td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $sale->status === 'paid' ? 'success' : 'danger' }}">{{ ucfirst($sale->status) }}</span>
                </td>
                <td><a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> Receipt</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No transactions found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $sales->links() }}
</div>
@endsection
