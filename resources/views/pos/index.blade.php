@extends('layouts.app')
@section('title', 'POS Checkout')
@section('page-title', 'POS Checkout')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <div class="card p-3 mb-3">
            <label class="form-label fw-semibold small">Scan Barcode / Enter Manually</label>
            <div class="d-flex gap-2">
                <input type="text" id="barcodeInput" class="form-control form-control-lg" placeholder="Scan or type barcode, then press Enter">
                <button type="button" id="openCameraBtn" class="btn btn-outline-dark" title="Scan using webcam (temporary, while you don't have a physical scanner)">
                    <i class="bi bi-camera-fill"></i> <span class="d-none d-md-inline">Scan with Camera</span>
                </button>
            </div>
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

<!-- Camera Scan Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-camera-fill me-1"></i> Scan with Webcam</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCameraBtn"></button>
            </div>
            <div class="modal-body text-center">
                <div id="cameraNotSupported" class="alert alert-warning small" style="display:none;"></div>
                <div id="reader" style="width:100%;"></div>
                <div class="small text-muted mt-2">Hold the barcode steady in front of the camera, well-lit if possible.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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

// ===== Scan sound feedback (generated in-browser, no audio file needed) =====
let audioCtx = null;
function playBeep(frequency, durationMs, type = 'sine') {
    try {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.type = type;
        oscillator.frequency.value = frequency;
        gainNode.gain.value = 0.15; // keep it gentle, not jarring
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + durationMs / 1000);
    } catch (e) {
        // audio not available (e.g. blocked by browser) - fail silently
    }
}
function playSuccessBeep() {
    playBeep(1000, 120); // short, high-pitched "success" beep like a real scanner
}
function playErrorBeep() {
    playBeep(220, 250, 'square'); // lower, buzzer-like tone for "not found"
}
function playWarningBeep() {
    // two quick beeps for expired/expiring-soon items
    playBeep(700, 100);
    setTimeout(() => playBeep(700, 100), 150);
}

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

// Barcode scanner (physical, USB): acts as a keyboard, typing fast then hitting Enter
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
            playErrorBeep();
            barcodeInput.focus();
            return;
        }

        if (data.product.is_expired) {
            playWarningBeep();
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
            playWarningBeep();
        } else {
            scanFeedback.textContent = 'Added: ' + data.product.name;
            scanFeedback.className = 'small mt-2 text-success';
            playSuccessBeep();
        }
        barcodeInput.focus(); // always return focus here so the next scan works immediately
    })
    .catch(() => {
        scanFeedback.textContent = 'Lookup failed. Check connection.';
        scanFeedback.className = 'small mt-2 text-danger';
        barcodeInput.focus();
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

// ===== Webcam Barcode Scanning (temporary backup while no physical scanner) =====
// Uses the html5-qrcode library (loaded via CDN above) instead of the browser's
// native BarcodeDetector API, since Brave and some other browsers strip that
// native API out for privacy reasons. This library works consistently across
// Chrome, Brave, Edge, and Firefox.
//
// This is entirely OPTIONAL and separate from the physical scanner flow above —
// once you have a real USB scanner, you simply stop clicking this button and use
// the barcode text box as normal. No code changes needed to "switch back."

const openCameraBtn = document.getElementById('openCameraBtn');
const cameraModalEl = document.getElementById('cameraModal');
const cameraModal = new bootstrap.Modal(cameraModalEl);
const cameraNotSupported = document.getElementById('cameraNotSupported');
let html5QrCode = null;
let cameraRunning = false;

openCameraBtn.addEventListener('click', async function () {
    cameraModal.show();
    cameraNotSupported.style.display = 'none';

    if (typeof Html5Qrcode === 'undefined') {
        cameraNotSupported.textContent = 'Camera scanning library failed to load. Check your internet connection, or just type the barcode manually.';
        cameraNotSupported.style.display = 'block';
        return;
    }

    try {
        html5QrCode = new Html5Qrcode('reader', {
            formatsToSupport: [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.ITF,
            ],
            verbose: false,
        });
        await html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 15, qrbox: { width: 300, height: 160 } },
            (decodedText) => {
                stopCamera();
                cameraModal.hide();
                lookupProduct(decodedText);
            },
            () => { /* per-frame "not found yet" - ignore, keeps scanning */ }
        );
        cameraRunning = true;
    } catch (err) {
        cameraNotSupported.textContent = 'Could not access the camera. Please allow camera permission in your browser and try again.';
        cameraNotSupported.style.display = 'block';
    }
});

function stopCamera() {
    if (html5QrCode && cameraRunning) {
        html5QrCode.stop().catch(() => {});
        cameraRunning = false;
    }
}

cameraModalEl.addEventListener('hidden.bs.modal', function () {
    stopCamera();
    barcodeInput.focus();
});
</script>
@endpush
