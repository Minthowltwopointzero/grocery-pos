@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" class="d-flex" style="max-width:320px;">
            <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search barcode or name" value="{{ request('search') }}">
            <button class="btn btn-sm btn-secondary">Search</button>
        </form>
        <div>
            <a href="{{ route('products.bulk-upload.form') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-upload"></i> Bulk Upload</a>
            <a href="{{ route('products.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
        <tr>
            <th>Barcode</th><th>Name</th><th>Cash Price</th><th>Credit Price</th><th>Stock</th><th>Expiry</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td><code>{{ $product->barcode }}</code></td>
                <td>{{ $product->name }}</td>
                <td>₱{{ number_format($product->cash_price, 2) }}</td>
                <td>₱{{ number_format($product->credit_price, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $product->isLowStock() ? 'danger' : 'success' }}">{{ $product->stock_quantity }}</span>
                </td>
                <td>
                    @if($product->expiration_date)
                        <span class="badge bg-{{ $product->isExpired() ? 'dark' : ($product->isExpiringSoon() ? 'warning' : 'success') }}">
                            {{ $product->expiration_date->format('M d, Y') }}
                        </span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td>
                    @if($product->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('products.label', $product) }}" class="btn btn-sm btn-outline-dark" title="Print barcode label"><i class="bi bi-upc"></i></a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    @if(auth()->user()->isAdmin())
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No products found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
