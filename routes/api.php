<?php

use App\Http\Controllers\BarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/bars', [BarController::class, 'index']);
Route::post('/bars', [BarController::class, 'store']);
Route::post('/bars/modif', [BarController::class, 'update']);

require __DIR__.'/api_auth.php';