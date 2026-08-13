@extends('layouts.app')
@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('content')
<div class="mb-3 no-print">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    <button onclick="window.print()" class="btn btn-sm btn-dark float-end"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card p-3">
            <div class="text-muted small">Total Inventory Value (at cash price)</div>
            <h4 class="text-success">₱{{ number_format($totalValueCash, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="text-muted small">Total Stock (all products, units)</div>
            <h4>{{ number_format($totalStockCount) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="text-muted small">Low Stock Items</div>
            <h4 class="text-danger">{{ $lowStockCount }}</h4>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-5">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">Product Status Breakdown</h6>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card p-3 h-100">
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Barcode</th><th>Product</th><th>Stock</th><th>Cash Price</th><th>Stock Value</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><code>{{ $p->barcode }}</code></td>
                        <td>{{ $p->name }}</td>
                        <td><span class="badge bg-{{ $p->isLowStock() ? 'danger' : 'success' }}">{{ $p->stock_quantity }}</span></td>
                        <td>₱{{ number_format($p->cash_price, 2) }}</td>
                        <td>₱{{ number_format($p->stock_quantity * $p->cash_price, 2) }}</td>
                        <td>
                            @if(!$p->is_active)
                                <span class="badge bg-secondary">Inactive</span>
                            @elseif($p->isExpired())
                                <span class="badge bg-dark">Expired</span>
                            @elseif($p->isExpiringSoon())
                                <span class="badge bg-warning text-dark">Expiring Soon</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('statusChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($statusCounts)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($statusCounts)) !!},
                    backgroundColor: ['#198754', '#dc3545', '#212529', '#ffc107', '#6c757d'],
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
</script>
@endpush
