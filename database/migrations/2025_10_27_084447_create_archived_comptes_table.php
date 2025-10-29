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
        // Create the archived_comptes table on the Neon connection only
        Schema::connection('neon')->create('archived_comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_compte')->unique();
            $table->uuid('user_id');
            $table->enum('type', ['epargne', 'cheque']);
            $table->decimal('solde', 15, 2);
            $table->string('devise', 10)->default('FCFA');
            $table->enum('statut', ['actif', 'bloque', 'ferme']);
            $table->timestamp('date_creation');
            $table->timestamp('date_fin_blocage')->nullable();
            $table->timestamp('date_archivage');
            $table->string('raison_archivage');
            $table->json('metadonnees')->nullable();
            $table->timestamp('archived_at');
            $table->timestamps();

            $table->index(['statut', 'date_fin_blocage']);
            $table->index('date_archivage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('neon')->dropIfExists('archived_comptes');
    }
};
