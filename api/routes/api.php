<?php

use App\Http\Controllers\Merchants\CreateMerchantController;
use App\Http\Controllers\Merchants\ShowMerchantController;
use Illuminate\Support\Facades\Route;

Route::prefix('merchants')->group(function () {
    Route::post('/', CreateMerchantController::class);
    Route::get('/{merchant}', ShowMerchantController::class);
});
