@extends('layouts.app')
@section('title', 'Credit Report')
@section('page-title', 'Credit / Utang Report')

@section('content')
<div class="mb-3 no-print">
    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    <button onclick="window.print()" class="btn btn-sm btn-dark float-end"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card p-3 mb-3">
    <div class="text-muted small">Total Outstanding Credit</div>
    <h3 class="text-danger">₱{{ number_format($totalOutstanding, 2) }}</h3>
</div>

<div class="card p-3">
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Customer</th><th>Office</th><th>Balance</th><th class="no-print"></th></tr></thead>
        <tbody>
        @forelse($customers as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->office ?? '-' }}</td>
                <td><span class="badge bg-danger">₱{{ number_format($c->balance, 2) }}</span></td>
                <td class="text-end no-print"><a href="{{ route('customers.show', $c) }}" class="btn btn-sm btn-outline-secondary">View Ledger</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No outstanding balances. 🎉</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
