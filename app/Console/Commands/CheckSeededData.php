<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\BankAccount;
use Illuminate\Console\Command;

class CheckSeededData extends Command
{
    protected $signature = 'app:check-seeded-data';
    protected $description = 'Check seeded data in the database';

    public function handle()
    {
        $this->info('Checking seeded data...');
        
        $clientCount = Client::count();
        $this->info("Number of clients: {$clientCount}");
        
        $accountCount = BankAccount::count();
        $this->info("Number of bank accounts: {$accountCount}");
        
        $clients = Client::with('bankAccounts')->get();
        foreach ($clients as $client) {
            $this->info("\nClient: {$client->name}");
            $this->info("Email: {$client->email}");
            $this->info("Number of accounts: " . $client->bankAccounts->count());
            
            foreach ($client->bankAccounts as $account) {
                $this->info("  Account number: {$account->account_number}");
                $this->info("  Balance: {$account->balance}");
            }
        }
    }
}