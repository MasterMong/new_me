<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'content_type' => fake()->randomElement(ContentType::cases())->value,
            'title' => fake()->sentence(5),
            'file_url' => fake()->url(),
            'duration_minutes' => fake()->randomFloat(2, 5, 120),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
