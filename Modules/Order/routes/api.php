<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout'])
        ->middleware('throttle:checkout');
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
});
