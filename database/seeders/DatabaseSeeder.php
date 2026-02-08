<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            DeveloperSeeder::class,
            LenderSeeder::class,
//            ProjectSeeder::class,
//            InvestmentSeeder::class,
        ]);
    }
}
