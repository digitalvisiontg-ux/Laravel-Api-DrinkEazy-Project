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
        //
        $biere = Categorie::where('nomCat', 'Bière')->first();
        $cocktail = Categorie::where('nomCat', 'Cocktail')->first();
        $vin = Categorie::where('nomCat', 'Vin')->first();

        Produit::create([
            'nomProd' => 'Bière Castel (500ml)',
            'prixBase' => 1500,
            'categorieId' => 1,
            'descProd' => 'Une bière blonde rafraîchissante très populaire.',
            'qtestock' => 50,
            'actif' => true
        ]);

        Produit::create([
            'nomProd' => 'Coca-Cola (33cl)',
            'prixBase' => 1000,
            'categorieId' => 2,
            'descProd' => 'Boisson gazeuse sucrée et pétillante.',
            'qteStock' => 100,
            'actif' => true
        ]);

        Produit::create([
            'nomProd' => 'Whisky Label 5 (1L)',
            'prixBase' => 5000,
            'categorieId' => 3,
            'descProd' => 'Un whisky écossais doux et équilibré.',
            'qteStock' => 20,
            'actif' => true
        ]);
    }
}
