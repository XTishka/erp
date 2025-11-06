<?php

namespace Database\Factories\Common;

use App\Models\Common\ClientTag;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientTag>
 */
class ClientTagFactory extends Factory
{
    protected $model = ClientTag::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'name' => ucfirst($this->faker->unique()->word),
            'color' => $this->faker->hexColor(),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function forCompany(?Company $company = null): self
    {
        return $this->state(function () use ($company) {
            $company ??= Company::first();

            return [
                'company_id' => $company?->id ?? 1,
            ];
        });
    }
}
