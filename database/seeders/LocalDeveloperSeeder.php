<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        User::updateOrCreate(
            ['email' => 'developer@email.com'],
            [
                'name' => 'Developer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
