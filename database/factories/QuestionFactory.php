<?php

namespace Database\Factories;

use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'question_type' => QuestionType::MultipleChoice->value,
            'question_text' => fake()->sentence().'?',
            'points' => 1,
            'grading_mode' => GradingMode::Auto->value,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
