@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers / Credit')

@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" class="d-flex" style="max-width:320px;">
            <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search name or office" value="{{ request('search') }}">
            <button class="btn btn-sm btn-secondary">Search</button>
        </form>
        @if(auth()->user()->isAdmin())
            <div>
                <a href="{{ route('customers.bulk-upload.form') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-upload"></i> Bulk Upload</a>
                <a href="{{ route('customers.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Add Customer</a>
            </div>
        @endif
    </div>

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Name</th><th>Office</th><th>Balance</th><th></th></tr></thead>
        <tbody>
        @forelse($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->office ?? '-' }}</td>
                <td>
                    <span class="badge bg-{{ $customer->balance > 0 ? 'danger' : 'success' }}">₱{{ number_format($customer->balance, 2) }}</span>
                </td>
                <td class="text-end">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Ledger</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No customers found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
