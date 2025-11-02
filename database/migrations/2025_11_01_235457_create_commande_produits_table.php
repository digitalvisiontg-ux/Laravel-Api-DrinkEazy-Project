<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commande_produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commandeId')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('produitId')->constrained('produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prixUnitaire', 10, 2);
            $table->decimal('sousTotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_produits');
    }
};
