<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            if (!Schema::hasColumn('comptes', 'date_debut_blocage')) {
                $table->timestamp('date_debut_blocage')->nullable()->after('statut');
            }

            if (!Schema::hasColumn('comptes', 'date_fin_blocage')) {
                $table->timestamp('date_fin_blocage')->nullable()->after('date_debut_blocage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            if (Schema::hasColumn('comptes', 'date_debut_blocage')) {
                $table->dropColumn('date_debut_blocage');
            }

            if (Schema::hasColumn('comptes', 'date_fin_blocage')) {
                $table->dropColumn('date_fin_blocage');
            }
        });
    }
};
