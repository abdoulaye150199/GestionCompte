<?php
// Run: php tools/add_softdeletes_to_comptes.php
// This script registers runtime DB connections from env and ensures 'deleted_at' and 'date_fermeture' exist on 'comptes' table.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Destination (railway or default DB)
$dest = [
    'driver' => getenv('RAILWAY_DB_CONNECTION') ?: getenv('DB_CONNECTION') ?: 'pgsql',
    'host' => getenv('RAILWAY_DB_HOST') ?: getenv('DB_HOST'),
    'port' => getenv('RAILWAY_DB_PORT') ?: getenv('DB_PORT'),
    'database' => getenv('RAILWAY_DB_DATABASE') ?: getenv('DB_DATABASE'),
    'username' => getenv('RAILWAY_DB_USERNAME') ?: getenv('DB_USERNAME'),
    'password' => getenv('RAILWAY_DB_PASSWORD') ?: getenv('DB_PASSWORD'),
    'schema' => getenv('RAILWAY_DB_SCHEMA') ?: getenv('DB_SCHEMA') ?: 'public',
    'sslmode' => getenv('RAILWAY_DB_SSLMODE') ?: getenv('DB_SSLMODE') ?: 'prefer',
];

$neon = [
    'driver' => getenv('NEON_DB_CONNECTION') ?: getenv('DB_CONNECTION') ?: 'pgsql',
    'host' => getenv('NEON_DB_HOST') ?: getenv('DB_HOST'),
    'port' => getenv('NEON_DB_PORT') ?: getenv('DB_PORT'),
    'database' => getenv('NEON_DB_DATABASE') ?: getenv('DB_DATABASE'),
    'username' => getenv('NEON_DB_USERNAME') ?: getenv('DB_USERNAME'),
    'password' => getenv('NEON_DB_PASSWORD') ?: getenv('DB_PASSWORD'),
    'schema' => getenv('NEON_DB_SCHEMA') ?: getenv('DB_SCHEMA') ?: 'public',
    'sslmode' => getenv('NEON_DB_SSLMODE') ?: getenv('DB_SSLMODE') ?: 'prefer',
];

Config::set('database.connections.tools_railway', [
    'driver' => $dest['driver'],
    'host' => $dest['host'],
    'port' => $dest['port'],
    'database' => $dest['database'],
    'username' => $dest['username'],
    'password' => $dest['password'],
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => $dest['schema'],
    'sslmode' => $dest['sslmode'],
]);

Config::set('database.connections.tools_neon', [
    'driver' => $neon['driver'],
    'host' => $neon['host'],
    'port' => $neon['port'],
    'database' => $neon['database'],
    'username' => $neon['username'],
    'password' => $neon['password'],
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => $neon['schema'],
    'sslmode' => $neon['sslmode'],
]);

$connections = ['tools_railway', 'tools_neon'];

foreach ($connections as $conn) {
    echo "\nChecking connection: $conn\n";
    try {
        DB::connection($conn)->getPdo();
    } catch (Exception $e) {
        echo " Cannot connect to $conn: " . $e->getMessage() . "\n";
        continue;
    }

    // Use schema builder for given connection
    $schema = Schema::connection($conn);
    if (! $schema->hasTable('comptes')) {
        echo "  Table 'comptes' does not exist on $conn, skipping.\n";
        continue;
    }

    // Add deleted_at if missing
    if (! $schema->hasColumn('comptes', 'deleted_at')) {
        echo "  Adding deleted_at to comptes on $conn...\n";
        DB::connection($conn)->statement('ALTER TABLE comptes ADD COLUMN deleted_at timestamp NULL');
        echo "  deleted_at added.\n";
    } else {
        echo "  deleted_at already exists on $conn.\n";
    }

    // Add date_fermeture if missing
    if (! $schema->hasColumn('comptes', 'date_fermeture')) {
        echo "  Adding date_fermeture to comptes on $conn...\n";
        DB::connection($conn)->statement("ALTER TABLE comptes ADD COLUMN date_fermeture timestamp NULL");
        echo "  date_fermeture added.\n";
    } else {
        echo "  date_fermeture already exists on $conn.\n";
    }

    // Add archived boolean if missing
    if (! $schema->hasColumn('comptes', 'archived')) {
        echo "  Adding archived to comptes on $conn...\n";
        // Use boolean with default false; Postgres accepts 'boolean' type
        DB::connection($conn)->statement("ALTER TABLE comptes ADD COLUMN archived boolean DEFAULT false");
        echo "  archived added.\n";
    } else {
        echo "  archived already exists on $conn.\n";
    }
}

echo "\nDone.\n";
