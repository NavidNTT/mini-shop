<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum', 'throttle:payment'])->prefix('payment')->group(function () {
    Route::post('/request', [PaymentController::class, 'request']);
    Route::post('/verify', [PaymentController::class, 'verify']);
});
