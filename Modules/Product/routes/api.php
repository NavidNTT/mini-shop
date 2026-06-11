<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;

Route::prefix('products')->group(function () {

    Route::get('/', [ProductController::class,'index']);

    Route::get('/{id}', [ProductController::class,'show']);

    Route::post('/', [ProductController::class,'store']);

    Route::put('/{id}', [ProductController::class,'update']);

    Route::delete('/{id}', [ProductController::class,'destroy']);

});
