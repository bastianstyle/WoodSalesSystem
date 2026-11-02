<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RsaKeyController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/verifikasi/{token}', [PublicVerificationController::class, 'show'])
    ->middleware('throttle:invoice-verification')
    ->name('verification.show');

Route::middleware(['auth', 'role:admin,kasir'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'download'])->name('invoices.pdf');

    Route::middleware('role:admin')->group(function () {
        Route::get('/keys', [RsaKeyController::class, 'index'])->name('keys.index');
        Route::post('/keys', [RsaKeyController::class, 'store'])->name('keys.store');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
