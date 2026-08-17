@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
<div class="card p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
        </div>
        <div class="col-auto">
            <select name="action" class="form-select form-select-sm">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>
                        {{ ucwords(str_replace('_', ' ', $action)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="User, details, or IP" value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                        <td>{{ $log->user_name }}</td>
                        <td>{{ $log->user_role ? ucfirst($log->user_role) : '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td>{{ $log->description }}</td>
                        <td class="text-nowrap">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No audit logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $auditLogs->links() }}
</div>
@endsection
