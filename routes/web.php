<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('dashboard')
            : redirect()->route('pos.index');
    }
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Admin-only management: creating, editing, deleting customers
    // + deleting products (cashiers can add/edit but not delete products)
    // + Dashboard + Sales History + Audit Log + Reports (cashiers no longer see these)
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'show'])->name('sales.receipt');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary'])->name('reports.sales-summary');
        Route::get('/reports/best-selling', [ReportController::class, 'bestSelling'])->name('reports.best-selling');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/credit', [ReportController::class, 'credit'])->name('reports.credit');

        Route::get('/customers/bulk-upload', [CustomerController::class, 'bulkUploadForm'])->name('customers.bulk-upload.form');
        Route::post('/customers/bulk-upload', [CustomerController::class, 'bulkUpload'])->name('customers.bulk-upload.store');
        Route::get('/customers/bulk-upload/template', [CustomerController::class, 'bulkUploadTemplate'])->name('customers.bulk-upload.template');
        Route::resource('customers', CustomerController::class)->except(['show', 'index']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // POS - available to admin and cashier
    // (this also includes product management except delete, and viewing the
    // customer list + credit ledger + recording payments)
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/products/bulk-upload', [ProductController::class, 'bulkUploadForm'])->name('products.bulk-upload.form');
        Route::post('/products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload.store');
        Route::get('/products/bulk-upload/template', [ProductController::class, 'bulkUploadTemplate'])->name('products.bulk-upload.template');
        Route::get('/products/bulk-upload/restock-template', [ProductController::class, 'bulkUploadRestockTemplate'])->name('products.bulk-upload.restock-template');
        Route::get('/products/{product}/label', [ProductController::class, 'label'])->name('products.label');
        Route::resource('products', ProductController::class)->except(['show', 'destroy']);

        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/api/products/lookup', [ProductController::class, 'findByBarcode'])->name('products.lookup');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/{customer}/payments', [CustomerController::class, 'addPayment'])->name('customers.payments.store');
    });
});
