<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->decimal('montant', 15, 2);
            $table->string('type');
            $table->timestamps();

            // Clé étrangère
            $table->foreign('compte_id')
                  ->references('id')
                  ->on('comptes')
                  ->onDelete('cascade');
        });

        // ✅ Ajouter la contrainte manuellement après la création
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('CREDIT', 'DEBIT'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
