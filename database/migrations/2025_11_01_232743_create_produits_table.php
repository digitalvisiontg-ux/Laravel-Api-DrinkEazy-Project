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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nomProd'); // Nom du produit
            $table->string('taille')->nullable(); // Ex: 500ml, 1L...
            $table->decimal('prixBase', 10, 2); // Prix sans promo
            $table->text('descProd')->nullable(); // Description du produit
            $table->integer('qteStock')->default(0); // Quantité en stock
            $table->boolean('actif')->default(true); // Produit visible ou non
            $table->string(column: 'imageUrl')->nullable(); // Image du produit
            $table->foreignId('categorieId')->constrained('categories')->onDelete('cascade'); // Relation vers catégorie
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
