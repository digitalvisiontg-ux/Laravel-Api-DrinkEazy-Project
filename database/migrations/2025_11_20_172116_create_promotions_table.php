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

            // TYPE DE PROMOTION
            $table->enum('type', ['reduction_prix', 'achat_offert', 'happy_hour']);

            // RÉDUCTION DE PRIX
            $table->enum('type_reduction', ['pourcentage', 'montant_fixe'])->nullable();
            $table->decimal('valeur_reduction', 10, 2)->nullable();

            // N ACHETÉS — N OFFERTS
            $table->integer('quantite_achat')->nullable();
            $table->integer('quantite_offerte')->nullable();

            // HAPPY HOUR
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();

            // PORTÉE DE LA PROMO (produit, catégorie…)
            $table->string('cible_type');   // ex: App\Models\Produit
            $table->unsignedBigInteger('cible_id'); // ex: 15

            // PÉRIODE
            $table->dateTime('debut');
            $table->dateTime('fin');

            // ÉTAT
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
