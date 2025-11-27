<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $later = Carbon::now()->addDays(7);

        // 🔹 Récupération des catégories
        $biere = Categorie::where('nomCat', 'Bière')->first();
        $cocktail = Categorie::where('nomCat', 'Cocktail')->first();
        $vin = Categorie::where('nomCat', 'Vin')->first();

        // 🔹 Récupération des produits
        $produitsBiere = Produit::where('categorieId', $biere->id)->get();
        $produitsCocktail = Produit::where('categorieId', $cocktail->id)->get();
        $produitsVin = Produit::where('categorieId', $vin->id)->get();

        // ----------------------------
        // PROMOTIONS SUR PRODUITS
        // ----------------------------

        // Produit 1 : réduction pourcentage
        Promotion::create([
            'type' => 'reduction_prix',
            'type_reduction' => 'pourcentage',
            'valeur_reduction' => 20,
            'cible_type' => Produit::class,
            'cible_id' => $produitsBiere[0]->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);

        // Produit 2 : réduction montant fixe
        Promotion::create([
            'type' => 'reduction_prix',
            'type_reduction' => 'montant_fixe',
            'valeur_reduction' => 500,
            'cible_type' => Produit::class,
            'cible_id' => $produitsCocktail[0]->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);

        // Produit 3 : happy hour
        Promotion::create([
            'type' => 'happy_hour',
            'heure_debut' => '18:00',
            'heure_fin' => '20:00',
            'cible_type' => Produit::class,
            'cible_id' => $produitsVin[0]->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);

        // ----------------------------
        // PROMOTIONS SUR CATEGORIES
        // ----------------------------

        // Catégorie Bière : nAchatNOffert
        Promotion::create([
            'type' => 'achat_offert',
            'quantite_achat' => 2,
            'quantite_offerte' => 1,
            'cible_type' => Categorie::class,
            'cible_id' => $biere->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);

        // Catégorie Cocktail : réduction pourcentage
        Promotion::create([
            'type' => 'reduction_prix',
            'type_reduction' => 'pourcentage',
            'valeur_reduction' => 15,
            'cible_type' => Categorie::class,
            'cible_id' => $cocktail->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);

        // Catégorie Vin : happy hour
        Promotion::create([
            'type' => 'happy_hour',
            'heure_debut' => '19:00',
            'heure_fin' => '21:00',
            'cible_type' => Categorie::class,
            'cible_id' => $vin->id,
            'debut' => $now,
            'fin' => $later,
            'actif' => true,
        ]);
    }
}
