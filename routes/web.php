<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
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
