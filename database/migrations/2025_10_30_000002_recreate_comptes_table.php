<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // avoid transactions on managed Postgres 
    public $withinTransaction = false;

    public function up(): void
    {
        // Drop foreign keys first to avoid constraint issues
        // Support both possible transaction table names and only run if they exist
        if (Schema::hasTable('transactions')) {
            DB::statement('ALTER TABLE IF EXISTS transactions DROP CONSTRAINT IF EXISTS transactions_compte_id_foreign');
        }
        if (Schema::hasTable('account_transactions')) {
            DB::statement('ALTER TABLE IF EXISTS account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
        }
        if (Schema::hasTable('mouvements')) {
            DB::statement('ALTER TABLE IF EXISTS mouvements DROP CONSTRAINT IF EXISTS mouvements_compte_id_foreign');
        }
        
        // Backup existing data (if any)
        if (Schema::hasTable('comptes')) {
            DB::statement('CREATE TABLE IF NOT EXISTS comptes_backup AS SELECT * FROM comptes');
        }

        // Drop and recreate comptes table
        Schema::dropIfExists('comptes');
        Schema::create('comptes', function ($table) {
            $table->uuid('id')->primary();
            $table->string('numero_compte', 64)->unique();
            $table->string('titulaire_compte', 255);
            $table->string('type_compte', 64);
            $table->string('devise', 8);
            $table->dateTime('date_creation')->nullable();
            $table->string('statut_compte', 32)->default('actif');
            $table->text('motif_blocage')->nullable();
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('manager_id')->nullable();
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
        
        // Copy data back only if the backup exists and its id column is UUID
        $backupExists = false;
        $colTypeIsUuid = false;
        $res = DB::select("SELECT to_regclass('public.comptes_backup') AS name");
        if (!empty($res) && !empty($res[0]->name)) {
            $backupExists = true;
            $col = DB::select("SELECT data_type FROM information_schema.columns WHERE table_name = 'comptes_backup' AND column_name = 'id'");
            if (!empty($col) && isset($col[0]->data_type) && $col[0]->data_type === 'uuid') {
                $colTypeIsUuid = true;
            }
        }

        if ($backupExists && $colTypeIsUuid) {
            // safe to cast id::uuid and client_id::uuid
            DB::statement(<<<'SQL'
            INSERT INTO comptes 
            SELECT 
                id::uuid,
                numero_compte,
                titulaire_compte,
                type_compte,
                devise,
                date_creation::timestamp,
                statut_compte,
                motif_blocage,
                version,
                user_id::bigint,
                client_id::uuid,
                manager_id::bigint,
                is_admin_managed::boolean,
                solde::decimal,
                archived::boolean,
                date_fermeture::timestamp,
                date_debut_blocage::timestamp,
                date_fin_blocage::timestamp,
                date_deblocage::timestamp,
                created_at::timestamp,
                updated_at::timestamp,
                deleted_at::timestamp
            FROM comptes_backup
SQL
            );

            // Drop backup table
            DB::statement('DROP TABLE comptes_backup');
        } elseif ($backupExists && ! $colTypeIsUuid) {
            // Backup exists but IDs are not UUID — skipping automatic copy to avoid data corruption.
            \Illuminate\Support\Facades\Log::warning('comptes_backup present but id is not uuid; skipping automatic restore.');
            DB::statement('DROP TABLE IF EXISTS comptes_backup');
        }
        
        // Change compte_id to uuid in account_transactions if it exists
        if (Schema::hasTable('account_transactions')) {
            DB::statement('ALTER TABLE account_transactions ALTER COLUMN compte_id SET DATA TYPE uuid USING gen_random_uuid()');
        }

        // Restore foreign keys for account_transactions and mouvements
        Schema::table('account_transactions', function ($table) {
            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
        });
        if (Schema::hasTable('mouvements')) {
            Schema::table('mouvements', function ($table) {
                $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // No rollback since this is a fix
    }
};