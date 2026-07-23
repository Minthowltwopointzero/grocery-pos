@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')
<div class="card p-4" style="max-width:600px;">
    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Barcode</label>
            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Product Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label fw-semibold">Cash Price</label>
                <input type="number" step="0.01" min="0" name="cash_price" class="form-control" value="{{ old('cash_price') }}" required>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label fw-semibold">Credit Price</label>
                <input type="number" step="0.01" min="0" name="credit_price" class="form-control" value="{{ old('credit_price') }}" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Stock Quantity</label>
            <input type="number" min="0" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Expiration Date <span class="text-muted fw-normal">(optional)</span></label>
            <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date') }}">
        </div>
        <button class="btn btn-dark">Save Product</button>
        <a href="{{ route('products.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
