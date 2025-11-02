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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('nomPromo'); // Nom de la promo
            $table->enum('typePromo', ['pourcentage', 'montantFixe', 'nAchatNOffert', 'happyHour']); // Type de promotion
            $table->decimal('valeurPromo', 10, 2)->nullable(); // Réduction (% ou montant)
            $table->integer('qteAchat')->nullable(); // n achetés
            $table->integer('qteOfferte')->nullable(); // n offerts
            $table->dateTime('debutPromo');
            $table->dateTime('finPromo');
            $table->enum('ciblePromo', ['produit', 'categorie', 'bar']); // Sur quoi s’applique la promo
            $table->foreignId('categorieId')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('produitId')->nullable()->constrained('produits')->onDelete('cascade');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
