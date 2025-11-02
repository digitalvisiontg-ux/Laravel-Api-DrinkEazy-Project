<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    //
    // 🔹 Lister tous les produits
    public function listerTous()
    {
        try {
            $produits = Produit::with('categorie')
            ->where('actif', true)
            ->get();

        return response()->json([
                'success' => true,
                'message' => 'Vérification réussie ✅',
                'produits' => $produits,
            ]); 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage()
            ], 500);
        }
    }
 
    // 🔹 Lister les produits par catégorie
    public function listerParCategorie($categorieNom)
    {
        $categorie = Categorie::where('nomCat', $categorieNom)->first();

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie introuvable'
            ], 404);
        }

        $produits = Produit::where('categorieId', $categorie->id)
            ->where('actif', true)
            ->get();

        return response()->json($produits);
    }

    // 🔹 Lister les produits en promotion
    public function listerEnPromotion()
    {
        $produits = Produit::with('promotion') // suppose que chaque produit peut avoir une relation promotion
            ->whereHas('promotion', function ($query) {
                $query->where('actif', true);
            })
            ->where('actif', true)
            ->get();

        return response()->json($produits);
    }

    // 🔹 Ajouter un produit (admin)
    public function ajouter(Request $request)
    {
        $user = $request->user();

        // Vérification du rôle directement dans la méthode
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $validated = $request->validate([
            'nomProd' => 'required|string',
            'prixBase' => 'required|numeric|min:0',
            'categorieId' => 'required|exists:categories,id',
            'descProd' => 'nullable|string',
            'qtestock' => 'required|integer|min:0',
            'actif' => 'boolean'
        ]);

        $produit = Produit::create($validated);

        return response()->json([
            'message' => 'Produit créé avec succès ✅',
            'produit' => $produit
        ], 201);
    }

    public function modifier(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        $validated = $request->validate([
            'nomProd' => 'string',
            'prixBase' => 'numeric|min:0',
            'categorieId' => 'exists:categories,id',
            'descProd' => 'string|nullable',
            'qtestock' => 'integer|min:0',
            'actif' => 'boolean'
        ]);

        $produit->update($validated);

        return response()->json([
            'message' => 'Produit mis à jour ✅',
            'produit' => $produit
        ]);
    }

    public function masquerSiRupture($id, Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        if ($produit->qtestock <= 0) {
            $produit->update(['actif' => false]);
        }

        return response()->json(['message' => 'Vérification stock terminée', 'produit' => $produit]);
    }
}
