@extends('layouts.app')
@section('title', 'Sales Summary')
@section('page-title', 'Sales Summary Report')

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

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Total Sales</div>
            <h4 class="text-success">₱{{ number_format($grandTotal, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Transactions</div>
            <h4>{{ $grandTransactions }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Cash Sales</div>
            <h4 class="text-success">₱{{ number_format($grandCash, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Credit Sales</div>
            <h4 class="text-warning">₱{{ number_format($grandCredit, 2) }}</h4>
        </div>
    </div>
</div>

<div class="card p-3 mb-3">
    <h6 class="fw-bold mb-3">Daily Sales Trend</h6>
    <canvas id="salesChart" height="90"></canvas>
</div>

<div class="card p-3">
    <div class="text-muted small mb-2">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Transactions</th><th>Cash</th><th>Credit</th><th>Total</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('M d, Y (D)') }}</td>
                <td>{{ $row->transactions }}</td>
                <td>₱{{ number_format($row->cash_total, 2) }}</td>
                <td>₱{{ number_format($row->credit_total, 2) }}</td>
                <td class="fw-semibold">₱{{ number_format($row->total, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No sales in this date range.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('salesChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Cash',
                        data: {!! json_encode($chartCash) !!},
                        backgroundColor: '#198754',
                    },
                    {
                        label: 'Credit',
                        data: {!! json_encode($chartCredit) !!},
                        backgroundColor: '#ffc107',
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, ticks: { callback: (v) => '₱' + v } },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }
</script>
@endpush
