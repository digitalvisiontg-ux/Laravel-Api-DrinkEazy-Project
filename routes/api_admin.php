<?php

use App\Http\Controllers\Admin\CommandeStaffController;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {
    Route::apiResource('promotions', PromotionController::class);
});

Route::get('/staff/commandes', [CommandeStaffController::class, 'index']);

Route::patch('/staff/commandes/{id}/status', [CommandeStaffController::class, 'updateStatus']);

