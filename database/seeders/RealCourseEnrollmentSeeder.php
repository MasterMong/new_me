<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Enrolls the existing demo learner accounts (see UserSeeder) into the real
 * course from RealCourseSeeder, with a clean in_progress status and no
 * fabricated progress — unlike the old CourseSeeder/EnrollmentSeeder demo
 * pairing, there's no fake watch history, test attempts or certificate to
 * layer onto content that's actually real.
 */
class RealCourseEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::first();

        if (! $course) {
            return;
        }

        User::where('role', UserRole::Learner->value)->each(function (User $learner) use ($course) {
            Enrollment::firstOrCreate(
                ['user_id' => $learner->id, 'course_id' => $course->id],
                ['status' => EnrollmentStatus::InProgress->value, 'enrolled_at' => now()]
            );
        });
    }
}
