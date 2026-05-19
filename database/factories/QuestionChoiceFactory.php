<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionChoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'choice_text' => fake()->sentence(4),
            'is_correct' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => ['is_correct' => true]);
    }
}
