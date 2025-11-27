<?php

use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {
    Route::apiResource('promotions', PromotionController::class);
});