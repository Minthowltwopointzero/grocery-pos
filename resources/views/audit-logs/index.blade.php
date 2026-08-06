@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
<div class="card p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="user_id" class="form-select form-select-sm">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="action" class="form-select form-select-sm">
                <option value="">All Actions</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $a)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Date/Time</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP</th></tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-nowrap">{{ $log->created_at->format('M d, Y g:i:s A') }}</td>
                <td>{{ $log->user_name }}</td>
                <td><span class="badge bg-secondary text-uppercase">{{ $log->user_role ?? '-' }}</span></td>
                <td><span class="badge bg-info text-dark">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span></td>
                <td class="small">{{ $log->description }}</td>
                <td class="small text-muted">{{ $log->ip_address ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No activity recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
