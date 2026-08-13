@extends('layouts.app')
@section('title', 'Product Label')
@section('page-title', 'Barcode Label')

@push('styles')
<style>
    @media print {
        @page { margin: 0.2in; }
        body { background: #fff !important; }
        .content-area { padding: 0 !important; }
        .label-box { box-shadow: none !important; border: 1px dashed #999 !important; }
    }
    .label-box {
        width: 300px;
        text-align: center;
        padding: 1rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between mb-3 no-print">
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
    <button onclick="window.print()" class="btn btn-sm btn-dark"><i class="bi bi-printer"></i> Print Label</button>
</div>

<div class="card label-box mx-auto">
    <div class="fw-bold small text-truncate">{{ $product->name }}</div>
    <div class="fw-bold">₱{{ number_format($product->cash_price, 2) }}</div>
    <svg id="barcodeSvg" class="my-2"></svg>
    <div class="small text-muted">{{ $product->barcode }}</div>
</div>

<div class="text-center text-muted small mt-3 no-print">
    Print this on regular paper, cut it out, and stick it on the product (e.g. with tape) so it can be scanned at checkout.
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    if (typeof JsBarcode !== 'undefined') {
        JsBarcode('#barcodeSvg', @json($product->barcode), {
            format: 'CODE128',
            width: 2,
            height: 60,
            displayValue: false,
            margin: 5,
        });
    }
</script>
@endpush
