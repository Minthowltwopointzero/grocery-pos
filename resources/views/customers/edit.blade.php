@extends('layouts.app')
@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('content')
<div class="card p-4" style="max-width:500px;">
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Customer Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Office</label>
            <input type="text" name="office" class="form-control" value="{{ old('office', $customer->office) }}">
        </div>
        <button class="btn btn-dark">Update Customer</button>
        <a href="{{ route('customers.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
