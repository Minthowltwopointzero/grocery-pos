@extends('layouts.app')
@section('title', 'Sales Summary')
@section('page-title', 'Sales Summary Report')

@push('styles')
<style>
    .sales-chart-wrap {
        position: relative;
        height: 340px;
        width: 100%;
    }
    @media print {
        @page { size: portrait; margin: 10mm; }
        .sales-chart-card {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .sales-chart-wrap {
            width: 100% !important;
            max-width: 100% !important;
            height: 210px !important;
        }
        #salesChart {
            width: 100% !important;
            max-width: 100% !important;
            height: 210px !important;
        }
    }
</style>
@endpush
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

<div class="card p-3 mb-3 sales-chart-card">
    <h6 class="fw-bold mb-3">Daily Sales Trend</h6>
    <div class="sales-chart-wrap">
        <canvas id="salesChart"></canvas>
    </div>
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
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const cashGradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 340);
        cashGradient.addColorStop(0, 'rgba(25, 135, 84, 0.30)');
        cashGradient.addColorStop(1, 'rgba(25, 135, 84, 0.02)');
        const creditGradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 340);
        creditGradient.addColorStop(0, 'rgba(245, 158, 11, 0.28)');
        creditGradient.addColorStop(1, 'rgba(245, 158, 11, 0.02)');

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Cash Sales', data: {!! json_encode($chartCash) !!},
                        borderColor: '#198754', backgroundColor: cashGradient,
                        pointBackgroundColor: '#ffffff', pointBorderColor: '#198754',
                        pointBorderWidth: 3, pointRadius: 5, pointHoverRadius: 7,
                        borderWidth: 3, tension: 0.35, fill: true,
                    },
                    {
                        label: 'Credit Sales', data: {!! json_encode($chartCredit) !!},
                        borderColor: '#f59e0b', backgroundColor: creditGradient,
                        pointBackgroundColor: '#ffffff', pointBorderColor: '#f59e0b',
                        pointBorderWidth: 3, pointRadius: 5, pointHoverRadius: 7,
                        borderWidth: 3, tension: 0.35, fill: true,
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                layout: { padding: { top: 8, right: 12, bottom: 4, left: 4 } },
                scales: {
                    x: {
                        grid: { display: false }, border: { display: false },
                        ticks: { color: isDark ? '#9198a1' : '#64748b' },
                    },
                    y: {
                        beginAtZero: true, grace: '10%', border: { display: false },
                        grid: { color: isDark ? 'rgba(255,255,255,.08)' : 'rgba(15,23,42,.08)' },
                        ticks: {
                            color: isDark ? '#9198a1' : '#64748b', padding: 10,
                            callback: (value) => '₱' + Number(value).toLocaleString(),
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: isDark ? '#c9d1d9' : '#475569', usePointStyle: true,
                            pointStyle: 'circle', padding: 24, boxWidth: 9, boxHeight: 9,
                        },
                    },
                    tooltip: {
                        padding: 12,
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ₱${Number(context.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
                        },
                    },
                },
            },
        });

        window.addEventListener('beforeprint', () => {
            salesChart.resize();
        });
        window.addEventListener('afterprint', () => {
            salesChart.resize();
        });
    }
</script>
@endpush
