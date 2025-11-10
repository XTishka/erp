<?php

namespace Database\Factories\Common;

use App\Models\Common\ClientCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientCategory>
 */
class ClientCategoryFactory extends Factory
{
    protected $model = ClientCategory::class;

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
}
