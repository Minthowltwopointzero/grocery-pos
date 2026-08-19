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

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">Daily Collection Trend</h6>
            @if(count($dailyLabels) > 0)
                <div style="position:relative; height:220px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            @else
                <p class="text-muted small mb-0">No payments recorded in this date range.</p>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold mb-3">Top Customers by Payment</h6>
            @if(count($topCustomerLabels) > 0)
                <div style="position:relative; height:220px;">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            @else
                <p class="text-muted small mb-0">No payments recorded in this date range.</p>
            @endif
        </div>
    </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx && typeof Chart !== 'undefined') {
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyLabels) !!},
                datasets: [{
                    label: 'Collected',
                    data: {!! json_encode($dailyTotals) !!},
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#198754',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => '₱' + Number(ctx.raw).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => '₱' + Number(v).toLocaleString() },
                        grid: { color: 'rgba(0,0,0,.05)' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    const topCtx = document.getElementById('topCustomersChart');
    if (topCtx && typeof Chart !== 'undefined') {
        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topCustomerLabels) !!},
                datasets: [{
                    label: 'Total Paid',
                    data: {!! json_encode($topCustomerTotals) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    maxBarThickness: 22,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => '₱' + Number(ctx.raw).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { callback: (v) => '₱' + Number(v).toLocaleString() },
                        grid: { color: 'rgba(0,0,0,.05)' },
                    },
                    y: { grid: { display: false } },
                },
            },
        });
    }
</script>
@endpush
