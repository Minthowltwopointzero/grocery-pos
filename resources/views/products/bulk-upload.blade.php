@extends('layouts.app')

@section('title', 'Bulk Upload Products')
@section('page-title', 'Bulk Upload Products')

@section('content')

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-1 fw-semibold">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Import Products
                </h5>
                <small class="text-muted">
                    Import multiple products from a CSV file. Existing products are updated automatically using their barcode.
                </small>
            </div>
            <div class="card-body p-4">
                @if(session('bulkUploadErrors'))
                    <div class="alert alert-warning">
                        <strong>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Import completed with warnings
                        </strong>
                        <ul class="mt-2 mb-0 small">
                            @foreach(session('bulkUploadErrors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST"
                      action="{{ route('products.bulk-upload.store') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="border rounded-4 bg-light p-5 text-center">
                        <div class="mb-3">
                            <i class="bi bi-file-earmark-arrow-up display-4 text-secondary"></i>
                        </div>
                        <h5 class="fw-semibold">
                            Select CSV File
                        </h5>
                        <p class="text-muted small mb-4">
                            Choose the completed CSV template from your computer.
                        </p>
                        <input
                            type="file"
                            name="csv_file"
                            class="form-control"
                            accept=".csv"
                            required>
                        <div class="form-text mt-2">
                            Supported format: <strong>.csv</strong> • Maximum size: <strong>5 MB</strong>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('products.index') }}"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button class="btn btn-dark px-4">
                            <i class="bi bi-upload me-1"></i>
                            Import Products
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>
                    Import Information
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:48px;height:48px;">
                            <i class="bi bi-download text-primary"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">
                            Download Template
                        </h6>
                        <p class="small text-muted mb-3">
                            Use the official CSV template before importing products.
                        </p>
                        <a href="{{ route('products.bulk-upload.template') }}"
                           class="btn btn-outline-primary btn-sm">
                            Download CSV
                        </a>
                    </div>
                </div>
                <hr>
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">
                        Required Columns
                    </h6>
                    <div class="small">
                        <span class="badge bg-light text-dark border">barcode</span>
                        <span class="badge bg-light text-dark border">name</span>
                        <span class="badge bg-light text-dark border">cash_price</span>
                        <span class="badge bg-light text-dark border">credit_price</span>
                        <span class="badge bg-light text-dark border">stock_quantity</span>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Optional:
                        <code>expiration_date</code>
                        (YYYY-MM-DD)
                    </p>
                </div>
                <hr>
                <div>
                    <h6 class="fw-semibold mb-3">
                        Processing Rules
                    </h6>
                    <div class="d-flex mb-3">
                        <i class="bi bi-arrow-repeat text-success me-3"></i>
                        <small>
                            Products with existing barcodes are updated automatically.
                        </small>
                    </div>
                    <div class="d-flex mb-3">
                        <i class="bi bi-plus-circle text-primary me-3"></i>
                        <small>
                            New barcodes create new product records.
                        </small>
                    </div>
                    <div class="d-flex">
                        <i class="bi bi-exclamation-circle text-warning me-3"></i>
                        <small>
                            Invalid rows are skipped and included in the import report.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection