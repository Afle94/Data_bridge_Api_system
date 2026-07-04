<?php

use App\Http\Controllers\Api\AccountMasterApiController;
use App\Http\Controllers\Api\PurchaseRegisterApiController;
use App\Http\Controllers\Api\LedgerApiController;
use App\Http\Controllers\Api\PaymentRegisterApiController;
use App\Http\Controllers\Api\ReceiptRegisterApiController;
use App\Http\Controllers\Api\SaleRegisterApiController;
use Illuminate\Support\Facades\Route;

Route::post('/sale-register', [SaleRegisterApiController::class, 'store']);
Route::post('/purchase-register', [PurchaseRegisterApiController::class, 'store']);
Route::post('/receipt-register', [ReceiptRegisterApiController::class, 'store']);
Route::post('/payment-register', [PaymentRegisterApiController::class, 'store']);
Route::post('/ledgers', [LedgerApiController::class, 'store']);
Route::post('/account-masters', [AccountMasterApiController::class, 'store']);
