<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Produit;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    // 🔹 Lister tous les produits
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    // 🔹 Lister tous les produits
    public function listerTous()
    {
        try {
            $produits = Produit::with('categorie')
                ->where('actif', true)
                ->get()
                ->map(function ($produit) {

                    if ($produit->imageUrl) {
                        $produit->imageUrl = asset('storage/' . $produit->imageUrl);
                    }

                    // Utilisation du PromotionService
                    $promotions = $this->promotionService->getPromotionsForProduit($produit);
                    $produit->promotions_details = $promotions;
                    $produit->promotion_active = count($promotions) > 0;

                    // Calcul du prix final si réduction
                    $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');
                    $produit->prixFinal = $reduction ? $this->calculerPrixFinal($produit->prixBase, $reduction) : $produit->prixBase;

                    return $produit;
                });

            return response()->json([
                'success' => true,
                'message' => 'Produits récupérés avec leurs promotions ✅',
                'produits' => $produits,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculerPrixFinal($prixBase, $promotion)
    {
        switch ($promotion['type_reduction'] ?? null) {
            case 'pourcentage':
                return round($prixBase - ($prixBase * ($promotion['valeur_reduction'] / 100)), 2);
            case 'montant_fixe':
                return max(0, $prixBase - $promotion['valeur_reduction']);
            default:
                return $prixBase;
        }
    }

    // 🔹 Lister les produits par catégorie
    public function listerParCategorie(Request $request, $categorieId)
    {
        try {
            // ID réservé pour la catégorie "Promotion" (par défaut 1) — configurable via .env
            $promotionCategoryId = 1;

            // Si on demande la catégorie promotion via son id, retourner tous les produits qui ont une promotion active
            if ((string) $categorieId === (string) $promotionCategoryId) {
                $categorie = Categorie::find($promotionCategoryId);
                $categorieNom = $categorie ? $categorie->nomCat : 'Promotion';

                $produits = Produit::with('categorie')
                    ->where('actif', true)
                    ->get()
                    ->map(function ($produit) use ($categorie) {
                        if ($produit->imageUrl) {
                            $produit->imageUrl = asset('storage/' . $produit->imageUrl);
                        }

                        $promotions = $this->promotionService->getPromotionsForProduit($produit);
                        $produit->promotions_details = $promotions;
                        $produit->promotion_active = count($promotions) > 0;

                        $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');
                        $produit->prixFinal = $reduction ? $this->calculerPrixFinal($produit->prixBase, $reduction) : $produit->prixBase;

                        // Pour que le client (Flutter) voie ces produits comme appartenant
                        // à la catégorie "Promotion", on attache la relation catégorie ici.
                        if ($produit->promotion_active && $categorie) {
                            // setRelation pour s'assurer que toArray/include inclut la catégorie
                            $produit->setRelation('categorie', $categorie);
                        }

                        return $produit;
                    })
                    ->filter(function ($produit) {
                        return $produit->promotion_active;
                    })
                    ->values();

                return response()->json([
                    'success' => true,
                    'message' => 'Produits en promotion récupérés avec succès ✅',
                    'categorie' => $categorieNom,
                    'produits' => $produits,
                ]);
            }

            // Sinon comportement normal : produits de la catégorie demandée
            $categorie = Categorie::find($categorieId);
            if (!$categorie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catégorie introuvable'
                ], 404);
            }

            $enPromotion = $request->query('enPromotion', false);

            $produits = Produit::with('categorie')
                ->where('categorieId', $categorieId)
                ->where('actif', true)
                ->get()
                ->map(function ($produit) {
                    if ($produit->imageUrl) {
                        $produit->imageUrl = asset('storage/' . $produit->imageUrl);
                    }

                    $promotions = $this->promotionService->getPromotionsForProduit($produit);
                    $produit->promotions_details = $promotions;
                    $produit->promotion_active = count($promotions) > 0;

                    $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');
                    $produit->prixFinal = $reduction ? $this->calculerPrixFinal($produit->prixBase, $reduction) : $produit->prixBase;

                    return $produit;
                })
                ->filter(function ($produit) use ($enPromotion) {
                    return $enPromotion ? $produit->promotion_active : true;
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Produits filtrés récupérés avec succès ✅',
                'categorie' => $categorie->nomCat,
                'produits' => $produits,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des produits par catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 Ajouter un produit (avec imageUrl)
    public function ajouter(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validated = $request->validate([
                'nomProd' => 'required|string',
                'prixBase' => 'required|numeric|min:0',
                'categorieId' => 'required|exists:categories,id',
                'descProd' => 'nullable|string',
                'qteStock' => 'required|integer|min:0',
                'taille' => 'nullable|string',
                'actif' => 'boolean',
                'imageUrl' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // 🔹 Vérification duplication nom + taille
            $taille = $validated['taille'] ?? null;
            $exists = Produit::where('nomProd', $validated['nomProd'])
                ->where('taille', $taille)
                ->first();

            if ($exists) {
                return response()->json([
                    'message' => 'Un produit avec le même nom et la même taille existe déjà'
                ], 400);
            }

            // 🔹 Gestion de l’image
            $imagePath = null;
            if ($request->hasFile('imageUrl')) {
                $imagePath = $request->file('imageUrl')->store('produits', 'public');
            }

            $produit = Produit::create(array_merge($validated, [
                'imageUrl' => $imagePath,
            ]));

            // URL complète pour la réponse
            if ($produit->imageUrl) {
                $produit->imageUrl = asset('storage/' . $produit->imageUrl);
            }

            return response()->json([
                'message' => 'Produit créé avec succès ✅',
                'produit' => $produit
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la création du produit',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // 🔹 Modifier un produit (avec gestion d’imageUrl)
    public function modifier(Request $request, $id)
    {
        try {
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
                'qteStock' => 'integer|min:0',
                'taille' => 'nullable|string',
                'actif' => 'boolean',
                'imageUrl' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // 🔹 Vérification duplication nom + taille (autre produit)
            if (isset($validated['nomProd'])) {
                $taille = $validated['taille'] ?? $produit->taille;
                $exists = Produit::where('nomProd', $validated['nomProd'])
                    ->where('taille', $taille)
                    ->where('id', '!=', $id)
                    ->first();
                if ($exists) {
                    return response()->json([
                        'message' => 'Un autre produit avec le même nom et la même taille existe déjà ❌'
                    ], 400);
                }
            }

            // 🔹 Gestion de la nouvelle image
            if ($request->hasFile('imageUrl')) {
                if ($produit->imageUrl && Storage::disk('public')->exists($produit->imageUrl)) {
                    Storage::disk('public')->delete($produit->imageUrl);
                }

                $imagePath = $request->file('imageUrl')->store('produits', 'public');
                $validated['imageUrl'] = $imagePath;
            }

            $produit->update($validated);

            // Préparer la réponse avec URL complète pour l'image
            $produitResponse = $produit->toArray();
            if ($produit->imageUrl) {
                $produitResponse['imageUrl'] = asset('storage/' . $produit->imageUrl);
            }

            return response()->json([
                'message' => 'Produit mis à jour ✅',
                'produit' => $produitResponse
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la modification du produit',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // 🔹 Masquer si rupture de stock
    public function masquerSiRupture($id, Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $produit = Produit::find($id);
            if (!$produit) {
                return response()->json(['message' => 'Produit introuvable'], 404);
            }

            if ($produit->qteStock <= 0) {
                $produit->update(['actif' => false]);
            }

            return response()->json([
                'message' => 'Vérification stock terminée',
                'produit' => $produit
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la vérification du stock',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}