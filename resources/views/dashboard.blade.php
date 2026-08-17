@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Today's Sales</div>
            <h3 class="text-success" id="statTodaySales">₱{{ number_format($todaySales, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Transactions Today</div>
            <h3 id="statTodayTransactions">{{ $todayTransactions }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Low Stock Items</div>
            <h3 class="text-warning" id="statLowStockCount">{{ $lowStockCount }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Outstanding Credit</div>
            <h3 class="text-danger" id="statOutstandingCredit">₱{{ number_format($totalCreditOutstanding, 2) }}</h3>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Transactions</h6>
                <span class="small text-muted"><span class="live-dot"></span> Live</span>
            </div>
            <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Invoice</th><th>Cashier</th><th>Type</th><th>Total</th><th></th></tr></thead>
                <tbody id="recentSalesBody">
                @forelse($recentSales as $sale)
                    <tr>
                        <td class="text-nowrap">{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td><span class="badge bg-{{ $sale->payment_type === 'cash' ? 'success' : 'warning' }}">{{ ucfirst($sale->payment_type) }}</span></td>
                        <td class="text-nowrap">₱{{ number_format($sale->total_amount,2) }}</td>
                        <td><a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">No sales yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Low Stock Alerts</h6>
                <span class="badge bg-danger" id="lowStockBadge">{{ $lowStockProducts->count() }}</span>
            </div>
            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                    <tbody id="lowStockBody">
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
                <span class="badge bg-secondary" id="expirationBadge">{{ $expiredProducts->count() + $expiringSoonProducts->count() }}</span>
            </div>
            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Expires</th></tr></thead>
                    <tbody id="expirationBody">
                    @foreach($expiredProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-nowrap"><span class="badge bg-dark">Expired {{ $p->expiration_date->format('M d, Y') }}</span></td>
                        </tr>
                    @endforeach
                    @forelse($expiringSoonProducts as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-nowrap"><span class="badge bg-warning text-dark">{{ $p->expiration_date->format('M d, Y') }}</span></td>
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

@push('styles')
<style>
    .live-dot {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #22c55e;
        margin-right: 4px;
        animation: livePulse 1.5s infinite;
    }
    @keyframes livePulse {
        0% { opacity: 1; }
        50% { opacity: .3; }
        100% { opacity: 1; }
    }
</style>
@endpush

@push('scripts')
<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function refreshDashboard() {
    fetch('{{ route('dashboard.data') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            document.getElementById('statTodaySales').textContent = '₱' + data.today_sales;
            document.getElementById('statTodayTransactions').textContent = data.today_transactions;
            document.getElementById('statLowStockCount').textContent = data.low_stock_count;
            document.getElementById('statOutstandingCredit').textContent = '₱' + data.total_credit_outstanding;
            document.getElementById('lowStockBadge').textContent = data.low_stock_products.length;
            document.getElementById('expirationBadge').textContent = data.expired_products.length + data.expiring_soon_products.length;

            // Recent Transactions
            const recentBody = document.getElementById('recentSalesBody');
            if (data.recent_sales.length === 0) {
                recentBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center">No sales yet.</td></tr>';
            } else {
                recentBody.innerHTML = data.recent_sales.map(s => `
                    <tr>
                        <td class="text-nowrap">${escapeHtml(s.invoice_no)}</td>
                        <td>${escapeHtml(s.cashier)}</td>
                        <td><span class="badge bg-${s.payment_type === 'cash' ? 'success' : 'warning'}">${s.payment_type === 'cash' ? 'Cash' : 'Credit'}</span></td>
                        <td class="text-nowrap">₱${s.total}</td>
                        <td><a href="${s.receipt_url}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                `).join('');
            }

            // Low Stock Alerts
            const lowStockBody = document.getElementById('lowStockBody');
            if (data.low_stock_products.length === 0) {
                lowStockBody.innerHTML = '<tr><td colspan="2" class="text-muted text-center">All stocked up.</td></tr>';
            } else {
                lowStockBody.innerHTML = data.low_stock_products.map(p => `
                    <tr>
                        <td>${escapeHtml(p.name)}</td>
                        <td><span class="badge bg-danger">${p.stock_quantity}</span></td>
                    </tr>
                `).join('');
            }

            // Expiration Alerts
            const expirationBody = document.getElementById('expirationBody');
            let expirationHtml = '';
            data.expired_products.forEach(p => {
                expirationHtml += `<tr><td>${escapeHtml(p.name)}</td><td class="text-nowrap"><span class="badge bg-dark">Expired ${p.expiration_date}</span></td></tr>`;
            });
            data.expiring_soon_products.forEach(p => {
                expirationHtml += `<tr><td>${escapeHtml(p.name)}</td><td class="text-nowrap"><span class="badge bg-warning text-dark">${p.expiration_date}</span></td></tr>`;
            });
            if (expirationHtml === '') {
                expirationHtml = '<tr><td colspan="2" class="text-muted text-center">Nothing expiring soon.</td></tr>';
            }
            expirationBody.innerHTML = expirationHtml;
        })
        .catch(() => { /* silently skip a failed refresh, try again next interval */ });
}

// Poll every 3 seconds. Pauses automatically when the browser tab is hidden
// (e.g. cashier switched tabs) to avoid wasting requests in the background.
let dashboardInterval = setInterval(refreshDashboard, 3000);

document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
        clearInterval(dashboardInterval);
    } else {
        refreshDashboard();
        dashboardInterval = setInterval(refreshDashboard, 3000);
    }
});
</script>
@endpush
