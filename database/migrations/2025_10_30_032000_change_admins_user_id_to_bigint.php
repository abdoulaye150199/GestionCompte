<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the column is uuid or something else, drop and recreate as bigint to match users.id
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'user_id')) {
                // drop the column first to avoid type conflicts
                $table->dropColumn('user_id');
            }
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->uuid('user_id')->notNull()->after('id');
            // add foreign key if users.id is the referenced pk
            // use raw statement to avoid issues if constraint exists
            try {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore if FK cannot be created in some DB setups
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->after('id');
        });
    }
};
