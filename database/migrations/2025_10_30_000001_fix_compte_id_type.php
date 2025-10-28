<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // avoid transactions on managed Postgres 
    public $withinTransaction = false;

    public function up(): void
    {
        // Only run statements if the target tables exist to avoid errors
        // Drop any foreign key constraints that reference the comptes.id column
        if (\Illuminate\Support\Facades\Schema::hasTable('transactions')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_compte_id_foreign');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('account_transactions')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE account_transactions DROP CONSTRAINT IF EXISTS account_transactions_compte_id_foreign');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('mouvements')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE mouvements DROP CONSTRAINT IF EXISTS mouvements_compte_id_foreign');
        }

        // Change id column type to bigint only if comptes table exists and id is not already bigint
        if (\Illuminate\Support\Facades\Schema::hasTable('comptes')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE comptes ALTER COLUMN id SET DATA TYPE bigint USING id::bigint');
            } catch (\Exception $e) {
                // If conversion fails or is unnecessary, just skip and continue
                \Illuminate\Support\Facades\Log::warning('Skipping comptes.id type alteration: '.$e->getMessage());
            }
        }

        // Re-add foreign key constraints if tables exist
        if (\Illuminate\Support\Facades\Schema::hasTable('transactions')) {
            $exists = \Illuminate\Support\Facades\DB::select("SELECT 1 FROM pg_constraint WHERE conname = ?", ['transactions_compte_id_foreign']);
            if (empty($exists)) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE transactions ADD CONSTRAINT transactions_compte_id_foreign FOREIGN KEY (compte_id) REFERENCES comptes(id) ON DELETE CASCADE');
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('account_transactions')) {
            $exists = \Illuminate\Support\Facades\DB::select("SELECT 1 FROM pg_constraint WHERE conname = ?", ['account_transactions_compte_id_foreign']);
            if (empty($exists)) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE account_transactions ADD CONSTRAINT account_transactions_compte_id_foreign FOREIGN KEY (compte_id) REFERENCES comptes(id) ON DELETE CASCADE');
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('mouvements')) {
            $exists = \Illuminate\Support\Facades\DB::select("SELECT 1 FROM pg_constraint WHERE conname = ?", ['mouvements_compte_id_foreign']);
            if (empty($exists)) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE mouvements ADD CONSTRAINT mouvements_compte_id_foreign FOREIGN KEY (compte_id) REFERENCES comptes(id) ON DELETE CASCADE');
            }
        }
    }

    public function down(): void
    {
        // No need to implement down() since we're fixing a data issue
    }
};