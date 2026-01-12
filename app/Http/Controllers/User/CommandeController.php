<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'commentaire' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantite' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {

            $total = 0;
            $produitsCommande = [];

            foreach ($validated['items'] as $item) {
                $produit = Produit::lockForUpdate()->find($item['produit_id']);

                // ❌ Produit inactif
                if (!$produit || !$produit->actif) {
                    abort(400, "Produit indisponible");
                }

                // ❌ Stock insuffisant
                if ($produit->qteStock < $item['quantite']) {
                    abort(400, "Stock insuffisant pour {$produit->nomProd}");
                }

                // 🔹 Calcul prix final avec promotions
                $promotions = $this->promotionService->getPromotionsForProduit($produit);
                $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');

                $prixUnitaire = $reduction
                    ? $this->calculerPrixFinal($produit->prixBase, $reduction)
                    : $produit->prixBase;

                $ligneTotal = $prixUnitaire * $item['quantite'];
                $total += $ligneTotal;

                $produitsCommande[] = [
                    'produit_id' => $produit->id,
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $prixUnitaire,
                ];

                // 📉 Décrément stock immédiat
                $produit->decrement('qteStock', $item['quantite']);
            }

            // 🧾 Création commande
            $commande = Commande::create([
                'table_id' => $validated['table_id'],
                'commentaire_client' => $validated['commentaire'] ?? null,
                'status' => 'pending',
                'total' => $total,
            ]);

            // 🧾 Lignes commande
            foreach ($produitsCommande as $ligne) {
                $commande->produits()->create($ligne);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'commande_id' => $commande->id,
                'total' => $total
            ], 201);
        });
    }

    private function calculerPrixFinal($prixBase, $promotion)
    {
        return match ($promotion['type_reduction'] ?? null) {
            'pourcentage' => round($prixBase - ($prixBase * ($promotion['valeur_reduction'] / 100)), 2),
            'montant_fixe' => max(0, $prixBase - $promotion['valeur_reduction']),
            default => $prixBase,
        };
    }

    public function show($id)
{
    $commande = Commande::with([
            'table:id,numero_table',
            'produits.produit:id,nomProd,taille'
        ])
        ->findOrFail($id);

    return response()->json([
        'success' => true,
        'commande' => [
            'id' => $commande->id,
            'status' => $commande->status,
            'total' => $commande->total,
            'table' => $commande->table->numero_table,
            'produits' => $commande->produits,
            'created_at' => $commande->created_at
        ]
    ]);
}
}
