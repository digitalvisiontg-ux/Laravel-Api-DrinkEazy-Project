<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories if they don't already exist
        Categorie::create(['nomCat' => 'Bière']);
        Categorie::create(['nomCat' => 'Cocktail']);
        Categorie::create(['nomCat' => 'Vin']);
        Categorie::create(['nomCat' => 'Soft']);
        Categorie::create(['nomCat' => 'Spiritueux']);
    }
}
