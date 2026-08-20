@extends('layouts.app')
@section('title', 'User Accounts')
@section('page-title', 'User Accounts')

@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fw-bold">Manage Admin &amp; Cashier Logins</div>
        <a href="{{ route('users.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Add Account</a>
    </div>

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }} @if($user->id === auth()->id())<span class="badge bg-secondary ms-1">You</span>@endif</td>
                <td><code>{{ $user->username }}</code></td>
                <td><span class="badge bg-primary text-uppercase">{{ $user->role }}</span></td>
                <td>
                    @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Deactivated</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No accounts found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
