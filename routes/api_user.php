<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ProduitController;

Route::get('/produits', [ProduitController::class, 'listerTous']);
Route::get('/produits/categorie/{nom}', [ProduitController::class, 'listerParCategorie']);
Route::get('/produits/promotion', [ProduitController::class, 'listerEnPromotion']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Actions admin gérées à l'intérieur du controller
    Route::post('/produits', [ProduitController::class, 'ajouter']);
    Route::put('/produits/{id}', [ProduitController::class, 'modifier']);
    Route::put('/produits/{id}/masquer', [ProduitController::class, 'masquerSiRupture']);
});