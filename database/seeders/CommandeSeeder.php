<?php

namespace Database\Seeders;

use App\Models\Commande;
use App\Models\CommandeProduit;
use App\Models\Produit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crée une commande test
        $commande = Commande::create([
            'numeroTable' => 'A12',
            'methodePaiement' => 'cash',
            'statut' => 'en_attente',
            'total' => 0
        ]);

        // Ajoute des produits
        $produit1 = Produit::first(); // récupère le premier produit
        $produit2 = Produit::skip(1)->first(); // deuxième produit

        $sousTotal1 = $produit1->prix * 2;
        $sousTotal2 = $produit2->prix * 1;
        $total = $sousTotal1 + $sousTotal2;

        CommandeProduit::create([
            'commandeId' => $commande->id,
            'produitId' => $produit1->id,
            'quantite' => 2,
            'prixUnitaire' => $produit1->prixBase,
            'sousTotal' => $sousTotal1
        ]);

        CommandeProduit::create([
            'commandeId' => $commande->id,
            'produitId' => $produit2->id,
            'quantite' => 1,
            'prixUnitaire' => $produit2->prixBase,
            'sousTotal' => $sousTotal2
        ]);

        $commande->update(['total' => $total]);
    }
}
