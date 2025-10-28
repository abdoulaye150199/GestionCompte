<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deprecated duplicate migration kept as a no-op to avoid running the
        // same change twice. The conditional migration
        // 2025_10_25_204529_add_nci_to_clients_table_if_not_exists.php handles
        // adding the 'nci' column safely.
        return;
    }

    public function down(): void
    {
        // no-op
        return;
    }
};
