<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            AffiliationSeeder::class,
            LearnerGroupSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            QuestionSeeder::class,
            UserGroupMembershipSeeder::class,
            ContentGroupAccessSeeder::class,
            EnrollmentSeeder::class,
            LearnerProgressSeeder::class,
            CourseReviewAndCertificateSeeder::class,
        ]);
    }
}
