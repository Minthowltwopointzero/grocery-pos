@extends('layouts.app')
@section('title', 'Bulk Upload Products')
@section('page-title', 'Bulk Upload Products')

@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-upload me-1"></i> Upload CSV File</h6>

            @if(session('bulkUploadErrors') && count(session('bulkUploadErrors')) > 0)
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1">Some rows had issues and were skipped or adjusted:</div>
                    <ul class="mb-0 small">
                        @foreach(session('bulkUploadErrors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('products.bulk-upload.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="form-control" required>
                    <div class="form-text">Max file size: 5MB. Must be a .csv file with a header row.</div>
                </div>
                <button class="btn btn-dark"><i class="bi bi-upload me-1"></i> Upload &amp; Process</button>
                <a href="{{ route('products.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1"></i> How it works</h6>
            <ul class="small mb-3">
                <li>Download the template below and fill it in (or export your existing Excel sheet to CSV with matching column names).</li>
                <li>Required columns: <code>barcode</code>, <code>name</code>, <code>cash_price</code>, <code>credit_price</code>, <code>stock_quantity</code></li>
                <li>Optional column: <code>expiration_date</code> (format: YYYY-MM-DD, e.g. 2026-12-31)</li>
                <li><strong>If a barcode already exists</strong> in the system, that product will be <strong>updated</strong> (name, prices, stock, expiration) instead of creating a duplicate.</li>
                <li>If a barcode is new, a new product will be created.</li>
            </ul>
            <a href="{{ route('products.bulk-upload.template') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-download me-1"></i> Download CSV Template
            </a>
        </div>
    </div>
</div>
@endsection
