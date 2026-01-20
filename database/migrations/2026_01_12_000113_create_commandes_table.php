<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();


            $table->string('numero_commande', 30)->unique()->index();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->uuid('guest_token')->nullable();
            $table->json('guest_infos')->nullable();

            $table->foreignId('table_id')
                ->constrained('tables')
                ->cascadeOnDelete();

            $table->enum('status', [
                'in_progress',
                'completed',
                'paid',
                'cancelled'
            ])->default('in_progress');
            $table->text('commentaire_client')->nullable();

            $table->decimal('total', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
