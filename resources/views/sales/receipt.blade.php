@extends('layouts.app')
@section('title', 'Receipt ' . $sale->invoice_no)
@section('page-title', 'Receipt')
 
@push('styles')
<style>
    @media print {
        body { background: #ffffff !important; }
        .content-area { padding: 0 !important; }
        .receipt-box {
            max-width: 3.9in !important;
            margin-left: 0.15in !important;
            margin-right: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush
 
@section('content')
<div class="d-flex justify-content-end mb-2 no-print">
    <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Print Receipt</button>
</div>
 
<div class="card p-4 mx-auto receipt-box" style="max-width:420px; font-family: 'Courier New', monospace;">
    <div class="text-center mb-2">
        <h5 class="fw-bold mb-0">GROCERY POS</h5>
        <div class="small">Official Receipt</div>
    </div>
    <hr>
    <div class="small">
        <div>Invoice: <strong>{{ $sale->invoice_no }}</strong></div>
        <div>Date: {{ $sale->created_at->format('M d, Y g:ia') }}</div>
        <div>Cashier: {{ $sale->user->name }}</div>
        <div>Payment: {{ ucfirst($sale->payment_type) }}</div>
        @if($sale->customer)
            <div>Customer: {{ $sale->customer->name }} ({{ $sale->customer->office }})</div>
        @endif
    </div>
    <hr>
    <table class="table table-sm small mb-0">
        <thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <hr>
    <div class="d-flex justify-content-between fw-bold">
        <span>TOTAL</span><span>₱{{ number_format($sale->total_amount, 2) }}</span>
    </div>
    @if($sale->payment_type === 'cash')
        <div class="d-flex justify-content-between small">
            <span>Amount Paid</span><span>₱{{ number_format($sale->amount_paid, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between small">
            <span>Change</span><span>₱{{ number_format($sale->change_amount, 2) }}</span>
        </div>
    @else
        <div class="d-flex justify-content-between small {{ $sale->status === 'paid' ? 'text-success' : 'text-danger' }}">
            <span>Status</span>
            <span>
                @if($sale->status === 'paid')
                    PAID
                @elseif($sale->status === 'partial')
                    PARTIALLY PAID (On Credit)
                @else
                    UNPAID (On Credit)
                @endif
            </span>
        </div>
    @endif
    <hr>
    <div class="text-center small">Thank you for your purchase!</div>
</div> 

@endsection