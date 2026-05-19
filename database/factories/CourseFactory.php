<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'thumbnail_url' => 'https://picsum.photos/seed/course_'.fake()->numberBetween(1, 1000).'/800/450',
            'duration_hours' => fake()->randomFloat(1, 3, 40),
            'passing_score_pct' => 60,
            'has_test' => true,
            'require_review' => true,
            'is_published' => false,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => true]);
    }
}
