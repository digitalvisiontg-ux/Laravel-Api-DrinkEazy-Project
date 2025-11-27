<?php

namespace App\Http\Controllers\User;

use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    /**
     * 🧩 Lister toutes les promotions
     */
    public function index()
    {
        $promos = Promotion::with(['produit', 'categorie'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($promos, 200);
    }

    /**
     * ➕ Créer une nouvelle promotion
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomPromo' => 'required|string|max:255',
            'typePromo' => 'required|in:pourcentage,montantFixe,nAchatNOffert,happyHour',
            'valeurPromo' => 'nullable|numeric|min:0',
            'qteAchat' => 'nullable|integer|min:1',
            'qteOfferte' => 'nullable|integer|min:1',
            'debutPromo' => 'required|date',
            'finPromo' => 'required|date|after:debutPromo',
            'ciblePromo' => 'required|in:produit,categorie,bar',
            'categorieId' => 'nullable|exists:categories,id',
            'produitId' => 'nullable|exists:produits,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation logique selon le type de promo
        if ($request->typePromo === 'nAchatNOffert' && (!$request->qteAchat || !$request->qteOfferte)) {
            return response()->json(['message' => 'Veuillez définir qteAchat et qteOfferte pour ce type de promotion.'], 400);
        }

        // Validation selon la cible
        if ($request->ciblePromo === 'produit' && !$request->produitId) {
            return response()->json(['message' => 'Veuillez sélectionner un produit.'], 400);
        }
        if ($request->ciblePromo === 'categorie' && !$request->categorieId) {
            return response()->json(['message' => 'Veuillez sélectionner une catégorie.'], 400);
        }

        $promo = Promotion::create($request->all());

        return response()->json([
            'message' => 'Promotion créée avec succès ✅',
            'promotion' => $promo
        ], 201);
    }

    /**
     * 🔍 Afficher une promotion spécifique
     */
    public function show($id)
    {
        $promo = Promotion::with(['produit', 'categorie'])->find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promotion introuvable'], 404);
        }

        return response()->json($promo, 200);
    }

    /**
     * ✏️ Modifier une promotion
     */
    public function update(Request $request, $id)
    {
        $promo = Promotion::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promotion non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nomPromo' => 'sometimes|string|max:255',
            'typePromo' => 'sometimes|in:pourcentage,montantFixe,nAchatNOffert,happyHour',
            'valeurPromo' => 'nullable|numeric|min:0',
            'qteAchat' => 'nullable|integer|min:1',
            'qteOfferte' => 'nullable|integer|min:1',
            'debutPromo' => 'nullable|date',
            'finPromo' => 'nullable|date|after:debutPromo',
            'ciblePromo' => 'sometimes|in:produit,categorie,bar',
            'categorieId' => 'nullable|exists:categories,id',
            'produitId' => 'nullable|exists:produits,id',
            'actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promo->update($request->all());

        return response()->json([
            'message' => 'Promotion mise à jour avec succès ✅',
            'promotion' => $promo
        ], 200);
    }

    /**
     * 🗑️ Supprimer une promotion
     */
    public function destroy($id)
    {
        $promo = Promotion::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promotion non trouvée'], 404);
        }

        $promo->delete();

        return response()->json(['message' => 'Promotion supprimée avec succès ✅'], 200);
    }

    /**
     * 🔁 Activer / désactiver une promotion
     */
    public function toggleActivation($id)
    {
        $promo = Promotion::find($id);

        if (!$promo) {
            return response()->json(['message' => 'Promotion non trouvée'], 404);
        }

        $promo->actif = !$promo->actif;
        $promo->save();

        return response()->json([
            'message' => $promo->actif ? 'Promotion activée ✅' : 'Promotion désactivée ❌',
            'promotion' => $promo
        ], 200);
    }

    /**
     * 📅 Récupérer les promotions actives en cours
     */
    public function activePromotions()
    {
        $now = Carbon::now();
        $promos = Promotion::where('actif', true)
            ->where('debutPromo', '<=', $now)
            ->where('finPromo', '>=', $now)
            ->with(['produit', 'categorie'])
            ->get();

        return response()->json($promos, 200);
    }
}
