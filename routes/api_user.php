<?php

use App\Http\Controllers\User\CommandeController;
use App\Http\Controllers\User\PromotionController;
use App\Http\Controllers\User\TableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ProduitController;

Route::get('/produits', [ProduitController::class, 'listerTous']);
Route::get('/produits/categorie/{categorieNom}', [ProduitController::class, 'listerParCategorie']);
Route::get('/produits/promotion', [ProduitController::class, 'listerEnPromotion']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Actions admin gérées à l'intérieur du controller
    Route::post('/produits', [ProduitController::class, 'ajouter']);
    Route::post('/produits/{id}', [ProduitController::class, 'modifier']);
    Route::put('/produits/{id}/masquer', [ProduitController::class, 'masquerSiRupture']);
});

Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/actives', [PromotionController::class, 'activePromotions']);
Route::get('/promotions/{id}', [PromotionController::class, 'show']);
Route::post('/promotions', [PromotionController::class, 'store']);
Route::put('/promotions/{id}', [PromotionController::class, 'update']);
Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);
Route::patch('/promotions/{id}/toggle', [PromotionController::class, 'toggleActivation']);


Route::prefix('table')->group(function () {
    Route::get('/verify/{token}', [TableController::class, 'verify']);
    Route::get('/verify-manual/{numeroTable}', [TableController::class, 'verifyManual']);
    Route::post('/store', [TableController::class, 'store']);
});


Route::post('/commandes', [CommandeController::class, 'store']);

Route::get('/commandes/{id}', [CommandeController::class, 'show']);