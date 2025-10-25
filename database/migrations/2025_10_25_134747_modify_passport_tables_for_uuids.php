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
        // Modifier la colonne user_id pour accepter les UUIDs
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->string('user_id')->change();
        });

        Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('access_token_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->bigInteger('user_id')->change();
        });

        Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
            $table->bigInteger('access_token_id')->change();
        });
    }
};
