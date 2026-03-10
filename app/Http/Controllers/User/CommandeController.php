<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommandeController extends Controller
{
    public function __construct(
        protected PromotionService $promotionService
    ) {
    }

    /* ============================================================
        STORE USER (AUTH OBLIGATOIRE)
    ============================================================ */
    public function storeUser(Request $request)
    {
        $user = auth()->user(); // garanti non null (middleware)

        return $this->createCommande(
            request: $request,
            userId: $user->id,
            guestToken: null
        );
    }

    /* ============================================================
        STORE GUEST (TOKEN OBLIGATOIRE)
    ============================================================ */
    public function storeGuest(Request $request)
    {
        $guestToken = $request->header('X-Guest-Token');

        if (!$guestToken) {
            return response()->json([
                'success' => false,
                'message' => 'Guest token requis'
            ], 400);
        }

        return $this->createCommande(
            request: $request,
            userId: null,
            guestToken: $guestToken
        );
    }

    /* ============================================================
        CORE LOGIC (DRY)
    ============================================================ */
    private function createCommande(Request $request, ?int $userId, ?string $guestToken)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'commentaire' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantite' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $userId, $guestToken) {

            $total = 0;
            $lignes = [];

            foreach ($validated['items'] as $item) {
                $produit = Produit::lockForUpdate()->find($item['produit_id']);

                if (!$produit || !$produit->actif) {
                    abort(400, 'Produit indisponible');
                }

                if ($produit->qteStock < $item['quantite']) {
                    abort(400, "Stock insuffisant pour {$produit->nomProd}");
                }

                $promotions = $this->promotionService->getPromotionsForProduit($produit);
                $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');

                $prixUnitaire = $reduction
                    ? $this->calculerPrixFinal($produit->prixBase, $reduction)
                    : $produit->prixBase;

                $total += $prixUnitaire * $item['quantite'];

                $lignes[] = [
                    'produit_id' => $produit->id,
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $prixUnitaire,
                ];

                $produit->decrement('qteStock', $item['quantite']);
            }

            $commande = Commande::create([
                'numero_commande' => $this->generateNumeroCommande(),
                'table_id' => $validated['table_id'],
                'user_id' => $userId,
                'guest_token' => $guestToken,
                'commentaire_client' => $validated['commentaire'] ?? null,
                'status' => 'in_progress',
                'total' => $total,
            ]);

            foreach ($lignes as $ligne) {
                $commande->produits()->create($ligne);
            }

            return response()->json([
                'success' => true,
                'commande' => $commande->load([
                    'table:id,numero_table,libelle',
                    'produits.produit:id,nomProd,taille'
                ])
            ], 201);
        });
    }

    /* ============================================================
        LIST USER / GUEST
    ============================================================ */
    public function index(Request $request)
    {
        $user = auth()->user();
        $guestToken = $request->header('X-Guest-Token');

        $query = Commande::with([
            'table:id,numero_table,libelle',
            'produits.produit:id,nomProd,taille'
        ])->orderByDesc('created_at');

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($guestToken) {
            $query->where('guest_token', $guestToken);
        } else {
            return response()->json([
                'success' => true,
                'commandes' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'commandes' => $query->get()
        ]);
    }

    /* ============================================================
        SHOW
    ============================================================ */
    public function show(int $id)
    {
        $commande = Commande::with([
            'table:id,numero_table,libelle',
            'produits.produit:id,nomProd,taille'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'commande' => $commande
        ]);
    }

    /* ============================================================
        GUEST ONLY
    ============================================================ */
    public function byGuest(string $token)
    {
        $commandes = Commande::with([
            'table:id,numero_table,libelle',
            'produits.produit:id,nomProd,taille'
        ])
            ->where('guest_token', $token)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'commandes' => $commandes
        ]);
    }

    /* ============================================================
        UTILS
    ============================================================ */
    private function calculerPrixFinal(float $prixBase, array $promotion): float
    {
        return match ($promotion['type_reduction'] ?? null) {
            'pourcentage' => round($prixBase * (1 - $promotion['valeur_reduction'] / 100), 2),
            'montant_fixe' => max(0, $prixBase - $promotion['valeur_reduction']),
            default => $prixBase,
        };
    }

    private function generateNumeroCommande(): string
    {
        $last = Commande::lockForUpdate()
            ->selectRaw("MAX(CAST(SUBSTRING(numero_commande, 2) AS UNSIGNED)) as max")
            ->value('max');

        return 'T' . (($last ?? 0) + 1);
    }

    public function update(Request $request, int $id)
    {
        try {
            $user = auth()->user();
            $guestToken = $request->header('X-Guest-Token');

            $commande = Commande::with('produits')->findOrFail($id);

            // Vérification propriétaire
            if ($user) {
                if ($commande->user_id !== $user->id) {
                    abort(403, 'Non autorisé');
                }
            } elseif ($guestToken) {
                if ($commande->guest_token !== $guestToken) {
                    abort(403, 'Non autorisé');
                }
            } else {
                abort(403, 'Non autorisé');
            }

            // Vérification statut
            if ($commande->status !== 'in_progress') {
                abort(400, 'Commande non modifiable');
            }

            $validated = $request->validate([
                'commentaire_client' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.produit_id' => 'required|exists:produits,id',
                'items.*.quantite' => 'required|integer|min:1',
            ]);

            return DB::transaction(function () use ($commande, $validated) {

                // 🔁 1. Restaurer ancien stock
                foreach ($commande->produits as $ligne) {
                    $ligne->produit()->increment('qteStock', $ligne->quantite);
                }

                // 🔥 2. Supprimer anciennes lignes
                $commande->produits()->delete();

                $total = 0;
                $nouvellesLignes = [];

                foreach ($validated['items'] as $item) {

                    $produit = Produit::lockForUpdate()->find($item['produit_id']);

                    if (!$produit || !$produit->actif) {
                        abort(400, 'Produit indisponible');
                    }

                    if ($produit->qteStock < $item['quantite']) {
                        abort(400, "Stock insuffisant pour {$produit->nomProd}");
                    }

                    $promotions = $this->promotionService->getPromotionsForProduit($produit);
                    $reduction = collect($promotions)->firstWhere('type', 'reduction_prix');

                    $prixUnitaire = $reduction
                        ? $this->calculerPrixFinal($produit->prixBase, $reduction)
                        : $produit->prixBase;

                    $total += $prixUnitaire * $item['quantite'];

                    $nouvellesLignes[] = [
                        'produit_id' => $produit->id,
                        'quantite' => $item['quantite'],
                        'prix_unitaire' => $prixUnitaire,
                    ];

                    $produit->decrement('qteStock', $item['quantite']);
                }

                foreach ($nouvellesLignes as $ligne) {
                    $commande->produits()->create($ligne);
                }

                $commande->update([
                    'commentaire_client' => $validated['commentaire_client'] ?? null,
                    'total' => $total,
                ]);

                return response()->json([
                    'success' => true,
                    'commande' => $commande->load([
                        'table:id,numero_table,libelle',
                        'produits.produit:id,nomProd,taille'
                    ])
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $user = auth()->user();
            $guestToken = $request->header('X-Guest-Token');

            $commande = Commande::with('produits')->findOrFail($id);

            // Vérification propriétaire
            if ($user) {
                if ($commande->user_id !== $user->id) {
                    abort(403, 'Non autorisé');
                }
            } elseif ($guestToken) {
                if ($commande->guest_token !== $guestToken) {
                    abort(403, 'Non autorisé');
                }
            } else {
                abort(403, 'Non autorisé');
            }

            if ($commande->status !== 'in_progress') {
                abort(400, 'Impossible de supprimer cette commande');
            }

            return DB::transaction(function () use ($commande) {

                // 🔁 Restaurer stock
                foreach ($commande->produits as $ligne) {
                    $ligne->produit()->increment('qteStock', $ligne->quantite);
                }

                // 🔥 Option 1 : suppression physique
                $commande->delete();

                // 🔥 Option 2 (recommandé pour audit) :
                // $commande->update(['status' => 'cancelled']);

                return response()->json([
                    'success' => true,
                    'message' => 'Commande supprimée'
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}