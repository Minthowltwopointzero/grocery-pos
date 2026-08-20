@extends('layouts.app')
@section('title', 'Edit Account')
@section('page-title', 'Edit Account')

@section('content')
<div class="card p-4" style="max-width:500px;">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
        </div>

        @if($user->id === auth()->id())
            <div class="alert alert-secondary small" style="border-radius:.6rem;">
                This is your own account — role and active status can't be changed here to avoid accidentally locking yourself out.
            </div>
        @else
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ $user->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (can log in)</label>
            </div>
        @endif

        <hr>
        <div class="mb-3">
            <label class="form-label fw-semibold">New Password <span class="text-muted fw-normal">(optional)</span></label>
            <input type="password" name="password" class="form-control">
            <div class="form-text">Leave blank to keep the current password.</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button class="btn btn-dark">Update Account</button>
        <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
