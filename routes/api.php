<?php

use App\Http\Controllers\Api\SaleRegisterApiController;
use Illuminate\Support\Facades\Route;

Route::post('/sale-register', [SaleRegisterApiController::class, 'store']);
