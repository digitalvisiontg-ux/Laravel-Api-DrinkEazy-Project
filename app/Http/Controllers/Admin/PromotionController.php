<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // Lister les promotions
    public function index()
    {
        return Promotion::with('cible')->get();
    }

    // Créer une promotion
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:reduction_prix,achat_offert,happy_hour',
            'type_reduction' => 'nullable|in:pourcentage,montant_fixe',
            'valeur_reduction' => 'nullable|numeric',
            'quantite_achat' => 'nullable|integer',
            'quantite_offerte' => 'nullable|integer',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'cible_type' => 'required|string', // App\Models\Produit ou App\Models\Categorie
            'cible_id' => 'required|integer',
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
            'actif' => 'boolean'
        ]);

        $promotion = Promotion::create($data);

        return response()->json($promotion, 201);
    }

    // Voir une promotion
    public function show(Promotion $promotion)
    {
        return $promotion->load('cible');
    }

    // Mettre à jour
    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'type' => 'in:reduction_prix,achat_offert,happy_hour',
            'type_reduction' => 'nullable|in:pourcentage,montant_fixe',
            'valeur_reduction' => 'nullable|numeric',
            'quantite_achat' => 'nullable|integer',
            'quantite_offerte' => 'nullable|integer',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'cible_type' => 'string',
            'cible_id' => 'integer',
            'debut' => 'nullable|date',
            'fin' => 'nullable|date|after_or_equal:debut',
            'actif' => 'boolean'
        ]);

        $promotion->update($data);

        return response()->json($promotion);
    }

    // Supprimer
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(null, 204);
    }
}
