@extends('layouts.app')

@section('title', 'Bulk Upload Customers')
@section('page-title', 'Bulk Upload Customers')

@push('styles')
<style>
    .customer-bulk-upload .card-header {
        color: var(--ink-900);
    }
    .customer-bulk-upload .import-details {
        background: #f8f9fa;
        color: #172033;
    }
    html[data-bs-theme="dark"] .customer-bulk-upload .card-header.bg-white {
        background-color: #1c2128 !important;
        border-color: var(--border-c) !important;
        color: #f0f6fc;
    }
    html[data-bs-theme="dark"] .customer-bulk-upload .import-details {
        background: #111d2e !important;
        border-color: #30363d !important;
        color: #e6edf3;
    }
    html[data-bs-theme="dark"] .customer-bulk-upload .text-secondary {
        color: #b1bac4 !important;
    }
</style>
@endpush

@section('content')

<div class="container-fluid customer-bulk-upload">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Bulk Customer Import</h3>
        <p class="text-muted mb-0">
            Import multiple customer records using a CSV template.
        </p>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">Customer Import</h5>
                </div>

                <div class="card-body">

                    @if(session('bulkUploadErrors'))
                        <div class="alert alert-warning">
                            <strong>Import completed with warnings.</strong>
                            <ul class="mt-2 mb-0">
                                @foreach(session('bulkUploadErrors') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row text-center mb-5">
                        <div class="col">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold text-primary fs-4">1</div>
                                <small class="text-muted d-block mb-2">Download Template</small>
                                <a href="{{ route('customers.bulk-upload.template') }}" class="btn btn-sm btn-outline-primary">
                                    Download
                                </a>
                            </div>
                        </div>

                        <div class="col">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold text-primary fs-4">2</div>
                                <small class="text-muted d-block mb-2">Complete the CSV</small>
                                <small class="text-secondary">Fill customer information.</small>
                            </div>
                        </div>

                        <div class="col">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-bold text-primary fs-4">3</div>
                                <small class="text-muted d-block mb-2">Upload File</small>
                                <small class="text-secondary">Import customers.</small>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('customers.bulk-upload.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">CSV File</label>

                            <input
                                type="file"
                                name="csv_file"
                                accept=".csv"
                                class="form-control form-control-lg"
                                required>

                            <div class="form-text">
                                Accepted format: CSV • Maximum size: 5 MB
                            </div>
                        </div>

                        <div class="border rounded import-details p-3 mb-4">
                            <div class="row">

                                <div class="col-md-6">
                                    <h6 class="fw-semibold">Required Fields</h6>

                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Name</td>
                                            <td class="text-end">
                                                <span class="badge bg-danger">Required</span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Office</td>
                                            <td class="text-end">
                                                <span class="badge bg-secondary">Optional</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="fw-semibold">Import Rules</h6>

                                    <ul class="small mb-0">
                                        <li>Header row is required.</li>
                                        <li>Every row creates a new customer.</li>
                                        <li>Starting balance is ₱0.00.</li>
                                        <li>Duplicate names are allowed.</li>
                                    </ul>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button class="btn btn-primary px-4">
                                <i class="bi bi-upload me-2"></i>
                                Import Customers
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold">Import Information</h6>
                </div>

                <div class="card-body">

                    <div class="mb-4">
                        <div class="fw-semibold mb-2">Before Importing</div>

                        <ul class="small mb-0">
                            <li>Download the template.</li>
                            <li>Keep the header names unchanged.</li>
                            <li>Save the file as CSV.</li>
                            <li>Review your data before importing.</li>
                        </ul>
                    </div>

                    <hr>

                    <div>
                        <div class="fw-semibold mb-2">Notes</div>

                        <small class="text-muted">
                            This process creates new customer records only.
                            Existing customers will not be updated automatically.
                        </small>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection