@extends('layouts.app')
@section('title', 'Best-Selling Products')
@section('page-title', 'Best-Selling Products Report')

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
            <button class="btn btn-sm btn-secondary">Apply</button>
        </div>
    </form>
</div>

<div class="card p-3 mb-3">
    <h6 class="fw-bold mb-3">Top 15 by Quantity Sold</h6>
    <canvas id="bestSellingChart" height="{{ max(200, count($chartLabels) * 32) }}"></canvas>
</div>

<div class="card p-3">
    <div class="text-muted small mb-2">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }} (Top 30)</div>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>#</th><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
        <tbody>
        @forelse($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->product_name }}</td>
                <td><span class="badge bg-primary">{{ $row->total_qty }}</span></td>
                <td class="fw-semibold">₱{{ number_format($row->total_revenue, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No sales in this date range.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('bestSellingChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Qty Sold',
                    data: {!! json_encode($chartQty) !!},
                    backgroundColor: '#0d6efd',
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }
</script>
@endpush
