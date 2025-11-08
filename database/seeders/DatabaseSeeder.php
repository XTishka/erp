<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserCompanySeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                LocalDeveloperSeeder::class,
                LocalCompanySeeder::class,
            ]);
        }
    }
}
