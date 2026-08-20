@extends('layouts.app')
@section('title', 'Add Account')
@section('page-title', 'Add Account')

@section('content')
<div class="card p-4" style="max-width:500px;">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
            <div class="form-text">Used to log in. No spaces, keep it simple (e.g. "cashier2").</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select" required>
                <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">At least 6 characters.</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-dark">Create Account</button>
        <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
