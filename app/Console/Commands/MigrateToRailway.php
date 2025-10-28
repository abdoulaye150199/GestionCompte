<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;

class MigrateToRailway extends Command
{
    /**
     * The name and signature of the console command.
     * --confirm will perform the destructive cleanup on the source (Neon).
     */
    protected $signature = 'migrate:railway {--confirm : Actually remove non-archived comptes from the source Neon DB after copying} {--tables= : Comma separated list of tables to copy (default: users,clients,comptes,transactions)} {--chunk=500 : Chunk size for copying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy selected tables from the default DB to a Railway DB and optionally keep only archived comptes in the source (Neon).';

    public function handle(): int
    {
        $this->info('Preparing migration to Railway...');

        // Build connection info.
        // Support two environment patterns:
        // - RAILWAY_DB_* (explicit destination) and NEON_DB_* (explicit source)
        // - or DB_* as destination and NEON_DB_* as source (common after edits)

        $destDriver = env('RAILWAY_DB_CONNECTION', env('DB_CONNECTION', 'pgsql'));
        $destHost = env('RAILWAY_DB_HOST', env('DB_HOST'));
        $destPort = env('RAILWAY_DB_PORT', env('DB_PORT', 5432));
        $destDatabase = env('RAILWAY_DB_DATABASE', env('DB_DATABASE'));
        $destUsername = env('RAILWAY_DB_USERNAME', env('DB_USERNAME'));
        $destPassword = env('RAILWAY_DB_PASSWORD', env('DB_PASSWORD'));
        $destSchema = env('RAILWAY_DB_SCHEMA', env('DB_SCHEMA', 'public'));
        $destSsl = env('RAILWAY_DB_SSLMODE', env('DB_SSLMODE', 'prefer'));

        // Source (Neon) can be provided with NEON_DB_*; if not present, try to use a `neon` connection or default DB (but default may point to Railway)
        $sourceDriver = env('NEON_DB_CONNECTION', env('DB_CONNECTION', 'pgsql'));
        $sourceHost = env('NEON_DB_HOST', env('DB_HOST'));
        $sourcePort = env('NEON_DB_PORT', env('DB_PORT', 5432));
        $sourceDatabase = env('NEON_DB_DATABASE', env('DB_DATABASE'));
        $sourceUsername = env('NEON_DB_USERNAME', env('DB_USERNAME'));
        $sourcePassword = env('NEON_DB_PASSWORD', env('DB_PASSWORD'));
        $sourceSchema = env('NEON_DB_SCHEMA', env('DB_SCHEMA', 'public'));
        $sourceSsl = env('NEON_DB_SSLMODE', env('DB_SSLMODE', 'prefer'));

        if (empty($destHost) || empty($destDatabase) || empty($destUsername)) {
            $this->error('Railway destination DB environment variables are not configured (RAILWAY_DB_* or DB_*).');
            return 1;
        }

        if (empty($sourceHost) || empty($sourceDatabase) || empty($sourceUsername)) {
            $this->error('Neon source DB environment variables are not configured (NEON_DB_* or DB_*).');
            return 1;
        }

        // Register both connections: 'railway' (destination) and 'neon' (source)
        config(['database.connections.railway' => [
            'driver' => $destDriver,
            'host' => $destHost,
            'port' => $destPort,
            'database' => $destDatabase,
            'username' => $destUsername,
            'password' => $destPassword,
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => $destSchema,
            'sslmode' => $destSsl,
        ],
        'neon' => [
            'driver' => $sourceDriver,
            'host' => $sourceHost,
            'port' => $sourcePort,
            'database' => $sourceDatabase,
            'username' => $sourceUsername,
            'password' => $sourcePassword,
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => $sourceSchema,
            'sslmode' => $sourceSsl,
        ]]);

        // Test connections
        try {
            DB::connection('neon')->getPdo();
            DB::connection('railway')->getPdo();
        } catch (\Exception $e) {
            $this->error('Unable to connect to one of the databases: ' . $e->getMessage());
            return 1;
        }

    $tablesOpt = $this->option('tables');
    $tables = $tablesOpt ? array_map('trim', explode(',', $tablesOpt)) : ['users', 'clients', 'comptes', 'transactions'];
        $chunk = (int) $this->option('chunk');

        $this->info('Tables to copy: ' . implode(', ', $tables));
        $this->info("Using chunk size: {$chunk}");

        foreach ($tables as $table) {
            $this->line("Copying table: {$table}");

            // Skip if the source table doesn't exist on Neon
            $schemaBuilder = DB::connection('neon')->getSchemaBuilder();
            if (! $schemaBuilder->hasTable($table)) {
                $this->info(" Source table '{$table}' not found on Neon, skipping.");
                continue;
            }

            $sourceCount = DB::connection('neon')->table($table)->count();
            $this->info(" Source rows: {$sourceCount}");

            $copied = 0;

            DB::connection('neon')->table($table)->orderBy('id')->chunk($chunk, function ($rows) use ($table, &$copied) {
                $rowsArray = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    // Remove columns that don't exist in destination
                    if ($table === 'users') {
                        unset($rowArray['activation_code'], $rowArray['activation_expires_at'], $rowArray['email'], $rowArray['nom'], $rowArray['prenom'], $rowArray['telephone']);
                    } elseif ($table === 'comptes') {
                        unset($rowArray['archived'], $rowArray['type'], $rowArray['client_id'], $rowArray['date_creation'], $rowArray['date_debut_blocage'], $rowArray['date_fermeture'], $rowArray['date_fin_blocage'], $rowArray['deleted_at'], $rowArray['devise'], $rowArray['is_admin_managed'], $rowArray['manager_id'], $rowArray['motif_blocage'], $rowArray['numero_compte'], $rowArray['statut_compte'], $rowArray['titulaire_compte'], $rowArray['type_compte'], $rowArray['version']);
                    }
                    $rowsArray[] = $rowArray;
                }

                if (empty($rowsArray)) {
                    return;
                }

                // insert to avoid duplicate primary key errors and type mismatches
                try {
                    foreach ($rowsArray as $row) {
                        // Convert id to string for UUID compatibility
                        if (isset($row['id'])) {
                            $row['id'] = (string) $row['id'];
                        }
                        if (isset($row['user_id'])) {
                            $row['user_id'] = (string) $row['user_id'];
                        }
                        // Skip if id is not a valid UUID format
                        if (isset($row['id']) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $row['id'])) {
                            continue;
                        }
                        DB::connection('railway')->table($table)->insert($row);
                    }
                    $copied += count($rowsArray);
                    $this->line('  Copied chunk: ' . count($rowsArray));
                } catch (\Exception $e) {
                    $this->error('  Failed to copy chunk for table ' . $table . ': ' . $e->getMessage());
                }
            });

            $destCount = DB::connection('railway')->table($table)->count();
            $this->info(" Done. Destination rows: {$destCount} (copied approx: {$copied})");
        }

        $this->info('Copy finished.');

        // If user requested cleanup on source (Neon) to keep only archived comptes
        if ($this->option('confirm')) {
            if (! $this->confirm('This will DELETE non-archived comptes and their transactions from the source (Neon). Are you sure you want to continue?')) {
                $this->info('Aborted destructive cleanup.');
                return 0;
            }

            $this->info('Pruning source (Neon) to keep only archived comptes...');

            // collect archived compte ids
            $archivedIds = DB::connection()->table('comptes')->whereRaw('archived = true')->pluck('id')->toArray();

            // delete transactions that are not for archived comptes (skip if table doesn't exist)
            try {
                $deletedTx = DB::connection()->table('transactions')->whereNotIn('compte_id', $archivedIds)->delete();
                $this->info(" Deleted transactions not linked to archived comptes: {$deletedTx}");
            } catch (\Exception $e) {
                $this->info(" Transactions table not found, skipping transaction cleanup");
            }

            // delete comptes not archived
            $deletedComptes = DB::connection()->table('comptes')->whereRaw('archived <> true')->delete();
            $this->info(" Deleted non-archived comptes: {$deletedComptes}");

            $this->info('Source pruning complete.');
        } else {
            $this->info('No destructive cleanup performed. To remove non-archived comptes from Neon, re-run with --confirm');
        }

        $this->info('Migration to Railway complete.');
        return 0;
    }
}
