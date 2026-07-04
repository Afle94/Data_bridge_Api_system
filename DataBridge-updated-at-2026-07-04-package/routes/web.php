<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentRegisterController;
use App\Http\Controllers\PurchaseRegisterController;
use App\Http\Controllers\ReceiptRegisterController;
use App\Http\Controllers\SaleRegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/purchase-register', [PurchaseRegisterController::class, 'index'])->name('purchases.register');
    Route::get('/receipt-register', [ReceiptRegisterController::class, 'index'])->name('receipts.register');
    Route::get('/payment-register', [PaymentRegisterController::class, 'index'])->name('payments.register');
    Route::get('/purchase-register/export/excel', [PurchaseRegisterController::class, 'exportExcel'])->name('purchases.export.excel');
    Route::get('/purchase-register/export/pdf', [PurchaseRegisterController::class, 'exportPdf'])->name('purchases.export.pdf');
    Route::get('/purchase-register/{purchaseRegister}/edit', [PurchaseRegisterController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchase-register/{purchaseRegister}', [PurchaseRegisterController::class, 'update'])->name('purchases.update');
    Route::delete('/purchase-register/{purchaseRegister}', [PurchaseRegisterController::class, 'destroy'])->name('purchases.destroy');
    Route::delete('/purchase-register', [PurchaseRegisterController::class, 'destroyAll'])->name('purchases.destroy-all');
    Route::get('/receipt-register/export/excel', [ReceiptRegisterController::class, 'exportExcel'])->name('receipts.export.excel');
    Route::get('/receipt-register/export/pdf', [ReceiptRegisterController::class, 'exportPdf'])->name('receipts.export.pdf');
    Route::get('/receipt-register/{receiptRegister}/edit', [ReceiptRegisterController::class, 'edit'])->name('receipts.edit');
    Route::put('/receipt-register/{receiptRegister}', [ReceiptRegisterController::class, 'update'])->name('receipts.update');
    Route::delete('/receipt-register/{receiptRegister}', [ReceiptRegisterController::class, 'destroy'])->name('receipts.destroy');
    Route::delete('/receipt-register', [ReceiptRegisterController::class, 'destroyAll'])->name('receipts.destroy-all');
    Route::get('/payment-register/export/excel', [PaymentRegisterController::class, 'exportExcel'])->name('payments.export.excel');
    Route::get('/payment-register/export/pdf', [PaymentRegisterController::class, 'exportPdf'])->name('payments.export.pdf');
    Route::get('/payment-register/{paymentRegister}/edit', [PaymentRegisterController::class, 'edit'])->name('payments.edit');
    Route::put('/payment-register/{paymentRegister}', [PaymentRegisterController::class, 'update'])->name('payments.update');
    Route::delete('/payment-register/{paymentRegister}', [PaymentRegisterController::class, 'destroy'])->name('payments.destroy');
    Route::delete('/payment-register', [PaymentRegisterController::class, 'destroyAll'])->name('payments.destroy-all');
    Route::get('/sale-register', [SaleRegisterController::class, 'index'])->name('sales.register');
    Route::get('/sale-register/export/excel', [SaleRegisterController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('/sale-register/export/pdf', [SaleRegisterController::class, 'pdfViewer'])->name('sales.export.pdf');
    Route::get('/sale-register/export/pdf-file', [SaleRegisterController::class, 'exportPdf'])->name('sales.export.pdf-file');
    Route::get('/sale-register/{saleRegister}/edit', [SaleRegisterController::class, 'edit'])->name('sales.edit');
    Route::put('/sale-register/{saleRegister}', [SaleRegisterController::class, 'update'])->name('sales.update');
    Route::delete('/sale-register/{saleRegister}', [SaleRegisterController::class, 'destroy'])->name('sales.destroy');
    Route::delete('/sale-register', [SaleRegisterController::class, 'destroyAll'])->name('sales.destroy-all');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
