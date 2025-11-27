<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        $biere = Categorie::where('nomCat', 'Bière')->first();
        $cocktail = Categorie::where('nomCat', 'Cocktail')->first();
        $vin = Categorie::where('nomCat', 'Vin')->first();
        $soft = Categorie::where('nomCat', 'Soft')->first();
        $spiritueux = Categorie::where('nomCat', 'Spiritueux')->first();

        // 🔹 Produits Bière
        $produitsBiere = [
            ['nomProd' => 'Bière Castel (500ml)', 'prixBase' => 1500, 'descProd' => 'Une bière blonde rafraîchissante très populaire.', 'qteStock' => 50],
            ['nomProd' => 'Bière Heineken (330ml)', 'prixBase' => 2000, 'descProd' => 'Bière blonde légère, parfaite pour l’apéritif.', 'qteStock' => 40],
            ['nomProd' => 'Bière Guinness (500ml)', 'prixBase' => 2500, 'descProd' => 'Bière brune au goût riche et crémeux.', 'qteStock' => 30],
        ];

        foreach ($produitsBiere as $p) {
            Produit::create(array_merge($p, ['categorieId' => $biere->id, 'actif' => true]));
        }

        // 🔹 Produits Cocktail
        $produitsCocktail = [
            ['nomProd' => 'Coca-Cola (33cl)', 'prixBase' => 1000, 'descProd' => 'Boisson gazeuse sucrée et pétillante.', 'qteStock' => 100],
            ['nomProd' => 'Mojito (250ml)', 'prixBase' => 3500, 'descProd' => 'Cocktail rafraîchissant à la menthe et au citron vert.', 'qteStock' => 20],
            ['nomProd' => 'Margarita (250ml)', 'prixBase' => 4000, 'descProd' => 'Cocktail classique à la tequila et citron vert.', 'qteStock' => 15],
        ];

        foreach ($produitsCocktail as $p) {
            Produit::create(array_merge($p, ['categorieId' => $cocktail->id, 'actif' => true]));
        }

        // 🔹 Produits Vin
        $produitsVin = [
            ['nomProd' => 'Whisky Label 5 (1L)', 'prixBase' => 5000, 'descProd' => 'Un whisky écossais doux et équilibré.', 'qteStock' => 20],
            ['nomProd' => 'Chardonnay (750ml)', 'prixBase' => 8000, 'descProd' => 'Vin blanc sec et fruité.', 'qteStock' => 10],
            ['nomProd' => 'Merlot (750ml)', 'prixBase' => 9000, 'descProd' => 'Vin rouge élégant et corsé.', 'qteStock' => 12],
        ];

        foreach ($produitsVin as $p) {
            Produit::create(array_merge($p, ['categorieId' => $vin->id, 'actif' => true]));
        }

        // 🔹 Produits Soft
        $produitsSoft = [
            ['nomProd' => 'Catezano (1L)', 'prixBase' => 5000, 'descProd' => 'Un whisky écossais doux et équilibré.', 'qteStock' => 20],
            ['nomProd' => 'Fire (750ml)', 'prixBase' => 8000, 'descProd' => 'Vin blanc sec et fruité.', 'qteStock' => 10],
            ['nomProd' => 'Whaley (750ml)', 'prixBase' => 9000, 'descProd' => 'Vin rouge élégant et corsé.', 'qteStock' => 12],
        ];

        foreach ($produitsSoft as $p) {
            Produit::create(array_merge($p, ['categorieId' => $soft->id, 'actif' => true]));
        }
        
        // 🔹 Produits Spiritueux
        $produitsSpiritueux = [
            ['nomProd' => 'Bisap (1L)', 'prixBase' => 5000, 'descProd' => 'Un whisky écossais doux et équilibré.', 'qteStock' => 20],
            ['nomProd' => 'Annanas (750ml)', 'prixBase' => 8000, 'descProd' => 'Vin blanc sec et fruité.', 'qteStock' => 10],
            ['nomProd' => 'Jurlet (750ml)', 'prixBase' => 9000, 'descProd' => 'Vin rouge élégant et corsé.', 'qteStock' => 12],
        ];

        foreach ($produitsSpiritueux as $p) {
            Produit::create(array_merge($p, ['categorieId' => $spiritueux->id, 'actif' => true]));
        }
    }
}
