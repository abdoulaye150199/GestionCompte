<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedDemoData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =============================
        // 1️⃣ Création de 5 utilisateurs
        // =============================
        $users = [];

        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'id' => Str::uuid()->toString(),
                'nom' => 'User' . $i,
                'prenom' => 'Test' . $i,
                'email' => 'user' . $i . '@example.com',
                'telephone' => '+22177000000' . $i,
                'password' => Hash::make('password'),
                'login' => 'user' . $i,
                'type' => 'client',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ✅ Upsert pour PostgreSQL : toutes les colonnes nécessaires listées
        DB::table('users')->upsert(
            $users,
            ['email'], // clé unique
            ['nom', 'prenom', 'telephone', 'password', 'login', 'type', 'updated_at']
        );

        // =========================================
        // 2️⃣ Récupérer les IDs des utilisateurs
        // =========================================
        $createdUsers = DB::table('users')->pluck('id')->all();

        // =========================================
        // 3️⃣ Créer des comptes (1 à 3 par utilisateur)
        // =========================================
        $comptes = [];
        foreach ($createdUsers as $userId) {
            $count = rand(1, 3);
            for ($j = 0; $j < $count; $j++) {
                $numero = 'C' . str_pad((string) rand(100000, 999999), 8, '0', STR_PAD_LEFT);
                $type = rand(0, 1) ? 'epargne' : 'cheque';

                $comptes[] = [
                    'id' => Str::uuid()->toString(),
                    'numero_compte' => $numero,
                    'titulaire_compte' => 'User Account ' . $numero,
                    'type_compte' => $type,
                    'devise' => 'CFA',
                    'user_id' => $userId,
                    'solde' => rand(1000, 2000000) / 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // ✅ Upsert sur "numero_compte" pour éviter les doublons
        DB::table('comptes')->upsert(
            $comptes,
            ['numero_compte'],
            ['titulaire_compte', 'type_compte', 'devise', 'user_id', 'solde', 'updated_at']
        );

// =========================================
// 4️⃣ Génération de transactions aléatoires
// =========================================
$createdComptes = DB::table('comptes')->pluck('id')->all();
$transactions = [];

foreach ($createdComptes as $compteId) {
    $ops = rand(2, 6);
    for ($k = 0; $k < $ops; $k++) {
        // ⚠️ Adapter au CHECK de PostgreSQL
        $type = rand(0, 1) ? 'CREDIT' : 'DEBIT';


        $amount = rand(1000, 500000) / 100;

        $transactions[] = [
            'id' => Str::uuid()->toString(),
            'montant' => $amount,
            'type' => $type,
            'compte_id' => $compteId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

if (Schema::hasTable('account_transactions')) {
    DB::table('account_transactions')->insert($transactions);
} elseif (Schema::hasTable('transactions')) {
    DB::table('transactions')->insert($transactions);
} else {
    $this->command->info('⚠️  Aucune table de transactions trouvée, insertion ignorée.');
}

$this->command->info('✅ Données de démonstration insérées avec succès !');
    }}