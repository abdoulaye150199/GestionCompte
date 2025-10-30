<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'nom')) {
                $table->string('nom')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('admins', 'nci')) {
                $table->string('nci')->unique()->nullable()->after('nom');
            }
            if (!Schema::hasColumn('admins', 'email')) {
                $table->string('email')->unique()->nullable()->after('nci');
            }
            if (!Schema::hasColumn('admins', 'telephone')) {
                $table->string('telephone')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('admins', 'adresse')) {
                $table->text('adresse')->nullable()->after('telephone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'adresse')) {
                $table->dropColumn('adresse');
            }
            if (Schema::hasColumn('admins', 'telephone')) {
                $table->dropUnique(['telephone']);
                $table->dropColumn('telephone');
            }
            if (Schema::hasColumn('admins', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('admins', 'nci')) {
                $table->dropUnique(['nci']);
                $table->dropColumn('nci');
            }
            if (Schema::hasColumn('admins', 'nom')) {
                $table->dropColumn('nom');
            }
        });
    }
};
