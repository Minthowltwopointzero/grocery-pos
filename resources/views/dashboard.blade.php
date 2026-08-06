@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Today's Sales</div>
            <h3 class="text-success">₱{{ number_format($todaySales, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Transactions Today</div>
            <h3>{{ $todayTransactions }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Low Stock Items</div>
            <h3 class="text-warning">{{ $lowStockCount }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Outstanding Credit</div>
            <h3 class="text-danger">₱{{ number_format($totalCreditOutstanding, 2) }}</h3>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Recent Transactions</h6>
            <table class="table table-sm">
                <thead><tr><th>Invoice</th><th>Cashier</th><th>Type</th><th>Total</th><th></th></tr></thead>
                <tbody>
                @forelse($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td><span class="badge bg-{{ $sale->payment_type === 'cash' ? 'success' : 'warning' }}">{{ ucfirst($sale->payment_type) }}</span></td>
                        <td>₱{{ number_format($sale->total_amount,2) }}</td>
                        <td><a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">No sales yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Low Stock Alerts</h6>
                <span class="badge bg-danger">{{ $lowStockProducts->count() }}</span>
            </div>
            <div style="max-height: 320px; overflow-y: auto;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                    <tbody>
                    @forelse($lowStockProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td><span class="badge bg-danger">{{ $p->stock_quantity }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center">All stocked up.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Expiration Alerts</h6>
                <span class="badge bg-secondary">{{ $expiredProducts->count() + $expiringSoonProducts->count() }}</span>
            </div>
            <div style="max-height: 320px; overflow-y: auto;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Expires</th></tr></thead>
                    <tbody>
                    @foreach($expiredProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td><span class="badge bg-dark">Expired {{ $p->expiration_date->format('M d, Y') }}</span></td>
                        </tr>
                    @endforeach
                    @forelse($expiringSoonProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $p->expiration_date->format('M d, Y') }}</span></td>
                        </tr>
                    @empty
                        @if($expiredProducts->isEmpty())
                            <tr><td colspan="2" class="text-muted text-center">Nothing expiring soon.</td></tr>
                        @endif
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
