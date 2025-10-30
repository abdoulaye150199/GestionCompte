<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Supprimer les contraintes FK existantes
        foreach (['transactions', 'account_transactions', 'mouvements'] as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE IF EXISTS {$table} DROP CONSTRAINT IF EXISTS {$table}_compte_id_foreign");
            }
        }

        // Sauvegarde des anciens comptes
        if (Schema::hasTable('comptes')) {
            DB::statement('CREATE TABLE IF NOT EXISTS comptes_backup AS SELECT * FROM comptes');
        }

        // Recréation de la table comptes
        Schema::dropIfExists('comptes');
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_compte', 64)->unique();
            $table->string('titulaire_compte', 255);
            $table->string('type_compte', 64);
            $table->string('devise', 8);
            $table->dateTime('date_creation')->nullable();
            $table->string('statut_compte', 32)->default('actif');
            $table->text('motif_blocage')->nullable();
            $table->integer('version')->default(1);

            // ✅ On passe user_id et manager_id en UUID
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->uuid('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('is_admin_managed')->default(false);
            $table->decimal('solde', 15, 2)->default(0);
            $table->boolean('archived')->default(false);
            $table->dateTime('date_fermeture')->nullable();
            $table->dateTime('date_debut_blocage')->nullable();
            $table->dateTime('date_fin_blocage')->nullable();
            $table->dateTime('date_deblocage')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ✅ Met à jour les colonnes dépendantes dans account_transactions et mouvements
        if (Schema::hasTable('account_transactions')) {
            DB::statement('ALTER TABLE account_transactions ALTER COLUMN compte_id TYPE uuid USING compte_id::uuid');
            Schema::table('account_transactions', function (Blueprint $table) {
                $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('mouvements')) {
            DB::statement('ALTER TABLE mouvements ALTER COLUMN compte_id TYPE uuid USING compte_id::uuid');
            Schema::table('mouvements', function (Blueprint $table) {
                $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
        Schema::dropIfExists('comptes_backup');
    }
};
