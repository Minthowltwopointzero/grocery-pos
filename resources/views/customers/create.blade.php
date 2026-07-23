@extends('layouts.app')
@section('title', 'Add Customer')
@section('page-title', 'Add Customer')

@section('content')
<div class="card p-4" style="max-width:500px;">
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Customer Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Office</label>
            <input type="text" name="office" class="form-control" value="{{ old('office') }}">
        </div>
        <button class="btn btn-dark">Save Customer</button>
        <a href="{{ route('customers.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
