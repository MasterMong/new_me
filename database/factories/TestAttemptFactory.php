<?php

namespace Database\Factories;

use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assessment_id' => Assessment::factory(),
            'attempt_number' => 1,
            'total_score' => null,
            'max_score' => null,
            'score_pct' => null,
            'star_rating' => 3,
            'status' => TestAttemptStatus::InProgress->value,
            'started_at' => now(),
            'submitted_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
