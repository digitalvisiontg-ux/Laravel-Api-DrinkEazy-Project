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
    ) {}

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
}