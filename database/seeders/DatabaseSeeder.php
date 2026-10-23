<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed only the restaurant's real configuration baseline.
     * Menu items, tables, hours, customers and orders are never fabricated.
     */
    public function run(): void
    {
        $this->call(GilasRestaurantSeeder::class);
    }
}
