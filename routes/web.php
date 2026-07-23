<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin-only management: creating, editing, deleting customers
    // + deleting products (cashiers can add/edit but not delete products)
    Route::middleware('role:admin')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['show', 'index']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // POS - available to admin and cashier
    // (this also includes product management except delete, and viewing the
    // customer list + credit ledger + recording payments)
    Route::middleware('role:admin,cashier')->group(function () {
        Route::resource('products', ProductController::class)->except(['show', 'destroy']);

        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/api/products/lookup', [ProductController::class, 'findByBarcode'])->name('products.lookup');

        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'show'])->name('sales.receipt');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/{customer}/payments', [CustomerController::class, 'addPayment'])->name('customers.payments.store');
    });
});