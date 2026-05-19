<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LearnerGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
