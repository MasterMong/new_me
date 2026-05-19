<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        $scenarios = [
            // Scenario A — full completion, certified
            [
                'email' => 'learner1@me-learning.go.th',
                'course' => $course1,
                'status' => EnrollmentStatus::Certified->value,
                'enrolled_at' => now()->subDays(60),
                'completed_at' => now()->subDays(10),
            ],
            // Scenario B — in progress, partially done
            [
                'email' => 'learner2@me-learning.go.th',
                'course' => $course1,
                'status' => EnrollmentStatus::InProgress->value,
                'enrolled_at' => now()->subDays(30),
                'completed_at' => null,
            ],
            // Scenario C — pending expert review on course 2
            [
                'email' => 'learner3@me-learning.go.th',
                'course' => $course2,
                'status' => EnrollmentStatus::InProgress->value,
                'enrolled_at' => now()->subDays(20),
                'completed_at' => null,
            ],
            // Scenario D — failed post-test
            [
                'email' => 'learner4@me-learning.go.th',
                'course' => $course1,
                'status' => EnrollmentStatus::InProgress->value,
                'enrolled_at' => now()->subDays(15),
                'completed_at' => null,
            ],
            // Scenario E — just enrolled, all locked
            [
                'email' => 'learner5@me-learning.go.th',
                'course' => $course1,
                'status' => EnrollmentStatus::InProgress->value,
                'enrolled_at' => now()->subDays(2),
                'completed_at' => null,
            ],
        ];

        foreach ($scenarios as $s) {
            $user = User::where('email', $s['email'])->first();
            Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $s['course']->id],
                [
                    'status' => $s['status'],
                    'enrolled_at' => $s['enrolled_at'],
                    'completed_at' => $s['completed_at'],
                ]
            );
        }
    }
}
