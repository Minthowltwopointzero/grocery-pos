@extends('layouts.app')
@section('title', 'POS Checkout')
@section('page-title', 'POS Checkout')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <div class="card p-3 mb-3">
            <label class="form-label fw-semibold small">Scan Barcode / Enter Manually</label>
            <input type="text" id="barcodeInput" class="form-control form-control-lg" placeholder="Scan or type barcode, then press Enter" autofocus>
            <div id="scanFeedback" class="small mt-2"></div>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
            <table class="table align-middle mb-0" id="cartTable">
                <thead>
                    <tr><th>Item</th><th style="width:110px;">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th><th></th></tr>
                </thead>
                <tbody id="cartBody">
                    <tr id="emptyRow"><td colspan="5" class="text-center text-muted py-4">Cart is empty. Scan a product to begin.</td></tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">Payment</h6>

            <div class="btn-group w-100 mb-3" role="group">
                <input type="radio" class="btn-check" name="paymentType" id="payCash" value="cash" checked>
                <label class="btn btn-outline-success" for="payCash">Cash</label>
                <input type="radio" class="btn-check" name="paymentType" id="payCredit" value="credit">
                <label class="btn btn-outline-warning" for="payCredit">Credit</label>
            </div>

            <div id="creditFields" style="display:none;">
                <label class="form-label small fw-semibold">Select Customer</label>
                <select id="customerSelect" class="form-select mb-3">
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->office }})</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Total</span>
                <span class="fs-4 fw-bold" id="totalDisplay">₱0.00</span>
            </div>

            <div id="cashFields" class="mt-3">
                <label class="form-label small fw-semibold">Amount Received</label>
                <input type="number" step="0.01" min="0" id="amountPaidInput" class="form-control form-control-lg mb-2" placeholder="0.00">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Change</span>
                    <span class="fw-bold" id="changeDisplay">₱0.00</span>
                </div>
            </div>

            <button id="checkoutBtn" class="btn btn-dark btn-lg w-100 mt-4" disabled>Complete Sale</button>
            <button id="clearCartBtn" class="btn btn-outline-secondary w-100 mt-2">Clear Cart</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = []; // { product_id, barcode, name, cash_price, credit_price, quantity, stock_quantity }

const barcodeInput = document.getElementById('barcodeInput');
const cartBody = document.getElementById('cartBody');
const scanFeedback = document.getElementById('scanFeedback');
const totalDisplay = document.getElementById('totalDisplay');
const changeDisplay = document.getElementById('changeDisplay');
const amountPaidInput = document.getElementById('amountPaidInput');
const checkoutBtn = document.getElementById('checkoutBtn');
const customerSelect = document.getElementById('customerSelect');
const creditFields = document.getElementById('creditFields');
const cashFields = document.getElementById('cashFields');

function currentPaymentType() {
    return document.querySelector('input[name="paymentType"]:checked').value;
}

function priceFor(item) {
    return currentPaymentType() === 'cash' ? item.cash_price : item.credit_price;
}

function calcTotal() {
    return cart.reduce((sum, item) => sum + priceFor(item) * item.quantity, 0);
}

function renderCart() {
    if (cart.length === 0) {
        cartBody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-muted py-4">Cart is empty. Scan a product to begin.</td></tr>';
    } else {
        cartBody.innerHTML = cart.map((item, idx) => {
            const price = priceFor(item);
            const subtotal = price * item.quantity;
            let expiryBadge = '';
            if (item.is_expired) {
                expiryBadge = `<span class="badge bg-dark ms-1">EXPIRED ${item.expiration_date ?? ''}</span>`;
            } else if (item.is_expiring_soon) {
                expiryBadge = `<span class="badge bg-warning text-dark ms-1">Expires ${item.expiration_date ?? ''}</span>`;
            }
            return `<tr>
                <td>${item.name}${expiryBadge}<div class="small text-muted">${item.barcode}</div></td>
                <td>
                    <input type="number" min="1" max="${item.stock_quantity}" value="${item.quantity}"
                        class="form-control form-control-sm qty-input" data-idx="${idx}">
                </td>
                <td class="text-end">₱${price.toFixed(2)}</td>
                <td class="text-end">₱${subtotal.toFixed(2)}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-danger remove-item" data-idx="${idx}"><i class="bi bi-x"></i></button></td>
            </tr>`;
        }).join('');
    }

    const total = calcTotal();
    totalDisplay.textContent = '₱' + total.toFixed(2);
    updateChange();
    checkoutBtn.disabled = cart.length === 0;

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function () {
            const idx = parseInt(this.dataset.idx);
            let qty = parseInt(this.value) || 1;
            if (qty > cart[idx].stock_quantity) {
                qty = cart[idx].stock_quantity;
                alert('Only ' + cart[idx].stock_quantity + ' in stock.');
            }
            if (qty < 1) qty = 1;
            cart[idx].quantity = qty;
            renderCart();
        });
    });

    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            cart.splice(parseInt(this.dataset.idx), 1);
            renderCart();
        });
    });
}

function updateChange() {
    const total = calcTotal();
    const paid = parseFloat(amountPaidInput.value) || 0;
    const change = paid - total;
    changeDisplay.textContent = '₱' + (change > 0 ? change.toFixed(2) : '0.00');
    changeDisplay.className = 'fw-bold ' + (change < 0 ? 'text-danger' : 'text-success');
}

amountPaidInput.addEventListener('input', updateChange);

document.querySelectorAll('input[name="paymentType"]').forEach(radio => {
    radio.addEventListener('change', function () {
        if (this.value === 'credit') {
            creditFields.style.display = 'block';
            cashFields.style.display = 'none';
        } else {
            creditFields.style.display = 'none';
            cashFields.style.display = 'block';
        }
        renderCart();
    });
});

// Barcode scanner: USB scanners act as a keyboard, typing fast then hitting Enter
barcodeInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = barcodeInput.value.trim();
        if (!barcode) return;
        lookupProduct(barcode);
        barcodeInput.value = '';
    }
});

function lookupProduct(barcode) {
    scanFeedback.textContent = 'Looking up...';
    scanFeedback.className = 'small mt-2 text-muted';

    fetch(`{{ route('products.lookup') }}?barcode=` + encodeURIComponent(barcode), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json().then(data => ({ status: res.status, data })))
    .then(({ status, data }) => {
        if (status !== 200 || !data.found) {
            scanFeedback.textContent = 'Product not found for barcode: ' + barcode;
            scanFeedback.className = 'small mt-2 text-danger';
            return;
        }
        if (data.product.is_expired) {
            const proceed = confirm(data.product.name + ' is EXPIRED (expired ' + data.product.expiration_date + '). Add to cart anyway?');
            if (!proceed) {
                scanFeedback.textContent = 'Skipped expired item: ' + data.product.name;
                scanFeedback.className = 'small mt-2 text-danger';
                return;
            }
        }
        addToCart(data.product);
        if (data.product.is_expired) {
            scanFeedback.textContent = 'Added (EXPIRED): ' + data.product.name;
            scanFeedback.className = 'small mt-2 text-danger';
        } else if (data.product.is_expiring_soon) {
            scanFeedback.textContent = 'Added (expiring ' + data.product.expiration_date + '): ' + data.product.name;
            scanFeedback.className = 'small mt-2 text-warning';
        } else {
            scanFeedback.textContent = 'Added: ' + data.product.name;
            scanFeedback.className = 'small mt-2 text-success';
        }
    })
    .catch(() => {
        scanFeedback.textContent = 'Lookup failed. Check connection.';
        scanFeedback.className = 'small mt-2 text-danger';
    });
}

function addToCart(product) {
    const existing = cart.find(item => item.product_id === product.id);
    if (existing) {
        if (existing.quantity < product.stock_quantity) {
            existing.quantity += 1;
        } else {
            alert('No more stock available for ' + product.name);
        }
    } else {
        if (product.stock_quantity < 1) {
            alert(product.name + ' is out of stock.');
            return;
        }
        cart.push({
            product_id: product.id,
            barcode: product.barcode,
            name: product.name,
            cash_price: product.cash_price,
            credit_price: product.credit_price,
            stock_quantity: product.stock_quantity,
            is_expired: product.is_expired,
            is_expiring_soon: product.is_expiring_soon,
            expiration_date: product.expiration_date,
            quantity: 1,
        });
    }
    renderCart();
}

document.getElementById('clearCartBtn').addEventListener('click', function () {
    if (cart.length && !confirm('Clear the entire cart?')) return;
    cart = [];
    amountPaidInput.value = '';
    renderCart();
});

checkoutBtn.addEventListener('click', function () {
    const paymentType = currentPaymentType();

    if (cart.length === 0) return;

    const payload = {
        payment_type: paymentType,
        items: cart.map(item => ({ product_id: item.product_id, quantity: item.quantity })),
    };

    if (paymentType === 'credit') {
        const customerId = customerSelect.value;
        if (!customerId) { alert('Please select a customer for credit sale.'); return; }
        payload.customer_id = customerId;
    } else {
        const paid = parseFloat(amountPaidInput.value) || 0;
        const total = calcTotal();
        if (paid < total) { alert('Amount received is less than the total.'); return; }
        payload.amount_paid = paid;
    }

    checkoutBtn.disabled = true;
    checkoutBtn.textContent = 'Processing...';

    fetch('{{ route('pos.checkout') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(res => res.json().then(data => ({ status: res.status, data })))
    .then(({ status, data }) => {
        if (status !== 200 || !data.success) {
            const errors = data.errors ? Object.values(data.errors).flat().join('\n') : 'Checkout failed.';
            alert(errors);
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Complete Sale';
            return;
        }
        // Redirect to printable receipt
        window.location.href = data.receipt_url;
    })
    .catch(() => {
        alert('Checkout failed. Please try again.');
        checkoutBtn.disabled = false;
        checkoutBtn.textContent = 'Complete Sale';
    });
});

barcodeInput.focus();
</script>
@endpush
