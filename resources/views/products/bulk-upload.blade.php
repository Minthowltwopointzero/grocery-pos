@extends('layouts.app')
@section('title', 'Bulk Upload Products')
@section('page-title', 'Bulk Upload Products')

@push('styles')
<style>
    .big-toggle-box {
        border: 2px solid #0d6efd;
        background: #eaf2ff;
        border-radius: .5rem;
        padding: 1rem;
    }
    .big-toggle-box .form-check-input {
        width: 3.2rem !important;
        height: 1.7rem !important;
        cursor: pointer;
        margin-top: 0;
    }
    .big-toggle-box .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .big-toggle-box .form-check-label {
        font-size: 1.05rem;
        vertical-align: middle;
        margin-left: .5rem;
        cursor: pointer;
    }
    .toggle-status {
        display: inline-block;
        font-weight: 700;
        padding: .15rem .6rem;
        border-radius: .3rem;
        margin-left: .5rem;
        background: #6c757d;
        color: #fff;
    }
    .big-toggle-box.is-on .toggle-status {
        background: #198754;
    }
</style>
@endpush

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

                <div class="big-toggle-box mb-3" id="restockToggleBox">
                    <div class="form-check form-switch d-flex align-items-center">
                        <input class="form-check-input" type="checkbox" role="switch" name="add_to_stock" value="1" id="addToStock">
                        <label class="form-check-label fw-semibold" for="addToStock">
                            Restock Mode (ADD to existing stock)
                        </label>
                        <span class="toggle-status" id="toggleStatusText">OFF</span>
                    </div>
                    <div class="form-text mt-2">
                        <strong>OFF</strong> = REPLACE the stock quantity (normal behavior).<br>
                        <strong>ON</strong> = ADD the uploaded quantity to existing stock — e.g. if a product already has 50 in stock and your CSV says <code>30</code>, it will become <strong>80</strong>. New products (barcode not found yet) are unaffected by this setting.
                    </div>
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
                <li><strong>Adding NEW products or fully replacing existing ones:</strong> Restock Mode OFF. Required columns: <code>barcode</code>, <code>name</code>, <code>cash_price</code>, <code>credit_price</code>, <code>stock_quantity</code>. Optional: <code>expiration_date</code> (YYYY-MM-DD).</li>
                <li><strong>Restocking existing products only:</strong> Turn Restock Mode ON — you only need <code>barcode</code> and <code>stock_quantity</code> in your CSV. Name/prices can be left out entirely; they'll stay unchanged.</li>
                <li><strong>Matching is done by barcode.</strong> If a barcode already exists, that product is updated instead of duplicated.</li>
                <li>If a barcode in Restock Mode doesn't match any existing product, that row will be skipped (a brand-new product still needs full details, so use the full template for those instead).</li>
            </ul>
            <a href="{{ route('products.bulk-upload.template') }}" class="btn btn-outline-dark btn-sm mb-2 w-100">
                <i class="bi bi-download me-1"></i> Download Full Template (new products)
            </a>
            <a href="{{ route('products.bulk-upload.restock-template') }}" class="btn btn-outline-success btn-sm w-100">
                <i class="bi bi-download me-1"></i> Download Restock Template (barcode + qty only)
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const restockCheckbox = document.getElementById('addToStock');
    const toggleStatusText = document.getElementById('toggleStatusText');
    const restockToggleBox = document.getElementById('restockToggleBox');

    restockCheckbox.addEventListener('change', function () {
        if (this.checked) {
            toggleStatusText.textContent = 'ON';
            restockToggleBox.classList.add('is-on');
        } else {
            toggleStatusText.textContent = 'OFF';
            restockToggleBox.classList.remove('is-on');
        }
    });
</script>
@endpush
