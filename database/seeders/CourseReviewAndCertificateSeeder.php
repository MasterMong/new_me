<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseReviewAndCertificateSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $learner1 = User::where('email', 'learner1@me-learning.go.th')->first();

        CourseReview::firstOrCreate(
            ['user_id' => $learner1->id, 'course_id' => $course1->id],
            [
                'rating' => 4,
                'comment' => 'หลักสูตรมีประโยชน์มาก เนื้อหาครอบคลุมและเข้าใจง่าย เหมาะสำหรับผู้ที่ต้องการพัฒนาทักษะการติดตามและประเมินผล',
            ]
        );

        Certificate::firstOrCreate(
            ['user_id' => $learner1->id, 'course_id' => $course1->id],
            [
                'certificate_number' => 'CERT-2026-00001',
                'full_name_on_cert' => 'นาย กมล ศรีสุข',
                'final_score_pct' => 75.0,
                'issued_date' => now()->subDays(10)->toDateString(),
                'pdf_url' => null,
            ]
        );
    }
}
