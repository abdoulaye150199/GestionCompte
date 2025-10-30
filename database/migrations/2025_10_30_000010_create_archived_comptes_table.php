<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Use the archive connection for this migration
    protected $connection = 'archive';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasTable('archived_comptes')) {
            Schema::connection($this->connection)->create('archived_comptes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('numero_compte')->nullable();
                $table->uuid('user_id')->nullable();
                $table->string('type')->nullable();
                $table->decimal('solde', 15, 2)->default(0);
                $table->string('devise')->nullable();
                $table->string('statut')->nullable();
                $table->timestamp('date_creation')->nullable();
                $table->timestamp('date_debut_blocage')->nullable();
                $table->timestamp('date_fin_blocage')->nullable();
                $table->text('motif_blocage')->nullable();
                $table->timestamp('date_archivage')->nullable();
                $table->text('raison_archivage')->nullable();
                $table->json('metadonnees')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps(6);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('archived_comptes');
    }
};
