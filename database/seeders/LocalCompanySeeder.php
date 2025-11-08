<?php

namespace Database\Seeders;

use App\Enums\Common\AddressType;
use App\Enums\Setting\EntityType;
use App\Models\User;
use App\Services\CompanyDefaultService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => 'developer@email.com'],
            [
                'name' => 'Developer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $company = $user->ownedCompanies()->firstOrCreate(
            ['name' => 'XTF'],
            [
                'personal_company' => $user->ownedCompanies()->doesntExist(),
            ],
        );

        $profile = $company->profiles()->firstOrCreate(
            ['name' => 'Default'],
            [
                'email' => 'info@xtf.com.ua',
                'phone_number' => '+380984854960',
                'entity_type' => EntityType::SoleProprietorship,
                'is_default' => true,
            ],
        );

        $profile->address()->updateOrCreate(
            ['type' => AddressType::General],
            [
                'company_id' => $company->id,
                'phone' => '+380984854960',
                'address_line_1' => 'Shrokaya str., app. 35',
                'city' => 'Dnipro',
                'state_id' => 4675,
                'postal_code' => '49080',
                'country_code' => 'UA',
            ],
        );

        if (! $company->default) {
            app(CompanyDefaultService::class)->createCompanyDefaults($company, $user, 'USD', 'UA', 'en');
        }

        if ($user->current_company_id !== $company->id) {
            $user->forceFill(['current_company_id' => $company->id])->save();
        }
    }
}
