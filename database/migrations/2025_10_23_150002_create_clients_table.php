<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally left blank.
        // This migration used to create the `clients` table with a bigint id.
        // The table is now created in a later migration using UUID primary keys
        // (see 2025_10_25_183835_create_clients_table.php). Keeping a no-op here
        // avoids attempting to create the table twice during a full migration run.
        return;
    }

    public function down(): void
    {
        // no-op
    }
};
