<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::apiResource('products', App\Http\Controllers\Api\ProductController::class)->middleware(['auth:sanctum']);

Route::get('products/barcode/{barcode}', [App\Http\Controllers\Api\ProductController::class, 'showByBarcode']);

Route::get('payment-methods', [App\Http\Controllers\Api\PaymentMethodController::class, 'index']);

Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class)->middleware(['auth:sanctum']);

Route::get('setting', [App\Http\Controllers\Api\SettingController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
