<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()
            ->withPersonalCompany(fn (CompanyFactory $factory) => $factory->withAdditionalProfiles())
            ->create([
                'name' => 'Test Company Owner',
                'email' => 'test@gmail.com',
                'password' => bcrypt('password'),
                'current_company_id' => 1,
            ]);
    }
}
