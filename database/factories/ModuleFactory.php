<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'module_number' => fake()->numberBetween(1, 9),
            'title' => 'บทที่ '.fake()->numberBetween(1, 9).': '.fake()->sentence(4),
            'description' => fake()->paragraph(),
            'thumbnail_url' => 'https://picsum.photos/seed/module_'.fake()->numberBetween(1, 1000).'/400/300',
            'is_required' => true,
            'requires_expert_review' => false,
            'is_sequential' => fake()->boolean(30),
            'max_test_attempts' => 3,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
