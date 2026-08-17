@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.sales-summary') }}" class="text-decoration-none">
            <div class="card p-4 h-100 text-center">
                <i class="bi bi-graph-up-arrow fs-1 text-success mb-2"></i>
                <h6 class="fw-bold mb-1">Sales Summary</h6>
                <div class="small text-muted">Daily totals, cash vs credit breakdown</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.best-selling') }}" class="text-decoration-none">
            <div class="card p-4 h-100 text-center">
                <i class="bi bi-award fs-1 text-warning mb-2"></i>
                <h6 class="fw-bold mb-1">Best-Selling Products</h6>
                <div class="small text-muted">Top products by quantity sold</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
            <div class="card p-4 h-100 text-center">
                <i class="bi bi-box-seam fs-1 text-primary mb-2"></i>
                <h6 class="fw-bold mb-1">Inventory Report</h6>
                <div class="small text-muted">Current stock levels and value</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.credit') }}" class="text-decoration-none">
            <div class="card p-4 h-100 text-center">
                <i class="bi bi-credit-card fs-1 text-danger mb-2"></i>
                <h6 class="fw-bold mb-1">Credit / Utang Report</h6>
                <div class="small text-muted">Customers with outstanding balances</div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('reports.payment-history') }}" class="text-decoration-none">
            <div class="card p-4 h-100 text-center">
                <i class="bi bi-cash-coin fs-1 text-success mb-2"></i>
                <h6 class="fw-bold mb-1">Payment History</h6>
                <div class="small text-muted">All credit payments, all customers combined</div>
            </div>
        </a>
    </div>
</div>
@endsection
