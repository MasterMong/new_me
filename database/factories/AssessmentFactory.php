<?php

namespace Database\Factories;

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'module_id' => null,
            'type' => fake()->randomElement(AssessmentType::cases())->value,
            'title' => 'แบบทดสอบ'.fake()->sentence(3),
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ];
    }
}
