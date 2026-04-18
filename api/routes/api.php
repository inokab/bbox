<?php

use App\Http\Controllers\Merchants\CreateMerchantController;
use App\Http\Controllers\Merchants\ShowMerchantController;
use App\Http\Controllers\Transactions\CreateTransactionController;
use App\Http\Controllers\Transactions\ListTransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('merchants')->group(function () {
    Route::post('/', CreateMerchantController::class);
    Route::get('/{merchant}', ShowMerchantController::class);

    Route::prefix('/{merchant}/transactions')->group(function () {
        Route::post('/', CreateTransactionController::class);
        Route::get('/', ListTransactionController::class);
    });
});
