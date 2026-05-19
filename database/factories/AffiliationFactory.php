<?php

namespace Database\Factories;

use App\Enums\AffiliationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'สพท.'.fake()->city().' เขต '.fake()->numberBetween(1, 4),
            'type' => fake()->randomElement(AffiliationType::cases())->value,
        ];
    }
}
