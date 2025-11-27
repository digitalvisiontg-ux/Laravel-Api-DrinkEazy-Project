<?php

namespace App\Services;

use App\Models\Produit;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Récupère les promotions applicables pour un produit
     * en fusionnant promos produit + promos catégorie selon Option B.
     *
     * @param Produit $produit
     * @return array
     */
    public function getPromotionsForProduit(Produit $produit): array
    {
        $now = Carbon::now();

        // 1. Promotions actives du produit
        $promosProduit = $produit->promotions()
            ->where('actif', true)
            ->where('debut', '<=', $now)
            ->where('fin', '>=', $now)
            ->get();

        // 2. Promotions actives de la catégorie
        $promosCategorie = $produit->categorie
            ->promotions()
            ->where('actif', true)
            ->where('debut', '<=', $now)
            ->where('fin', '>=', $now)
            ->get();

        // 3. Fusionner les deux collections
        $allPromos = $promosProduit->merge($promosCategorie);

        // 4. Appliquer Option B : 1 seule promo par type, priorité produit
        $promosFiltrees = collect();

        foreach (['reduction_prix', 'achat_offert', 'happy_hour'] as $type) {
            // On récupère toutes les promos de ce type
            $promosType = $allPromos->where('type', $type);

            if ($promosType->isNotEmpty()) {
                // Priorité produit
                $promoProduit = $promosType->firstWhere('cible_type', 'App\Models\Produit');
                if ($promoProduit) {
                    $promosFiltrees->push($promoProduit);
                } else {
                    // Sinon on prend la promo catégorie
                    $promosFiltrees->push($promosType->first());
                }
            }
        }

        // 5. Préparer le résultat prêt pour le front
        return $promosFiltrees->map(function ($promo) use ($produit) {
            return [
                'type' => $promo->type,
                'type_reduction' => $promo->type_reduction,
                'valeur_reduction' => $promo->valeur_reduction,
                'quantite_achat' => $promo->quantite_achat,
                'quantite_offerte' => $promo->quantite_offerte,
                'heure_debut' => $promo->heure_debut,
                'heure_fin' => $promo->heure_fin,
                'texte_badge' => $this->generateBadge($promo),
                'texte_description' => $this->generateDescription($promo, $produit),
            ];
        })->toArray();
    }

    /**
     * Génère le texte du badge à afficher
     */
    private function generateBadge($promo): string
    {
        switch ($promo->type) {
            case 'reduction_prix':
                return $promo->type_reduction === 'pourcentage'
                    ? '-' . $promo->valeur_reduction . '% 🎉'
                    : '-' . number_format($promo->valeur_reduction, 0, '.', '') . 'FCFA 🎉';
            case 'achat_offert':
                return $promo->quantite_achat . ' achetés ' . $promo->quantite_offerte . ' offerts';
            case 'happy_hour':
                return 'Happy Hour';
            default:
                return '';
        }
    }

    /**
     * Génère un texte descriptif de la promo
     */
    private function generateDescription($promo, Produit $produit): string
    {
        switch ($promo->type) {
            case 'reduction_prix':
                return 'Promo sur ' . $produit->nom . ': ' . $this->generateBadge($promo);
            case 'achat_offert':
                return 'Achetez ' . $promo->quantite_achat . ' et obtenez ' . $promo->quantite_offerte . ' offert';
            case 'happy_hour':
                return 'Happy Hour de ' . $promo->heure_debut . ' à ' . $promo->heure_fin;
            default:
                return '';
        }
    }
}
