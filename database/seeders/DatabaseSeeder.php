<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SeedDemoData;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure SeedDemoData does not depend on AdminSeeder, or adjust order if needed.
                $this->call(AdminSeeder::class);
                $this->call(SeedDemoData::class);
    }
}
