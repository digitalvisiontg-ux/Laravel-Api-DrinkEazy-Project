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
        Schema::table('commande_produits', function (Blueprint $table) {
            $table->unsignedInteger('quantite_offerte')->default(0)->after('quantite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commande_produits', function (Blueprint $table) {
            $table->dropColumn('quantite_offerte');
        });
    }
};
