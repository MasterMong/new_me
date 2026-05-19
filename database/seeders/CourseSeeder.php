<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\GradingMode;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseImage;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $course1 = Course::create([
            'title' => 'หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน',
            'description' => 'หลักสูตรสำหรับการพัฒนาศักยภาพนักติดตามและประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน เพื่อให้มีความรู้ ความเข้าใจ และทักษะในการติดตาม ประเมินผลอย่างมีประสิทธิภาพ',
            'thumbnail_url' => 'https://picsum.photos/seed/course_1/800/450',
            'duration_hours' => 6.0,
            'passing_score_pct' => 60,
            'has_test' => true,
            'require_review' => true,
            'is_published' => true,
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            CourseImage::create([
                'course_id' => $course1->id,
                'image_url' => 'https://picsum.photos/seed/gallery_1_'.$i.'/800/600',
                'sort_order' => $i * 10,
            ]);
        }

        $modules1 = [
            ['number' => 1, 'title' => 'ความรู้พื้นฐานการติดตามและประเมินผล', 'sort_order' => 1],
            ['number' => 2, 'title' => 'กระบวนการติดตามและประเมินผลการบริหาร', 'sort_order' => 2],
            ['number' => 3, 'title' => 'เครื่องมือและเทคนิคการประเมินผล', 'sort_order' => 3],
            ['number' => 4, 'title' => 'การเขียนรายงานการประเมินผล', 'sort_order' => 4],
            ['number' => 5, 'title' => 'การนำผลประเมินไปใช้พัฒนาการศึกษา', 'sort_order' => 5],
        ];

        foreach ($modules1 as $m) {
            $module = Module::create([
                'course_id' => $course1->id,
                'module_number' => $m['number'],
                'title' => $m['title'],
                'description' => 'เนื้อหาของ'.$m['title'],
                'thumbnail_url' => 'https://picsum.photos/seed/module_1_'.$m['number'].'/400/300',
                'is_required' => true,
                'requires_expert_review' => $m['number'] === 4,
                'max_test_attempts' => 3,
                'sort_order' => $m['sort_order'],
            ]);

            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Video->value,
                'title' => 'วิดีโอบรรยาย: '.$m['title'],
                'file_url' => 'https://example.com/videos/course1/module'.$m['number'].'.mp4',
                'duration_minutes' => 30.00,
                'sort_order' => 1,
            ]);

            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Document->value,
                'title' => 'เอกสารประกอบ: '.$m['title'],
                'file_url' => 'https://example.com/docs/course1/module'.$m['number'].'.pdf',
                'duration_minutes' => null,
                'sort_order' => 2,
            ]);
        }

        Assessment::create([
            'course_id' => $course1->id,
            'module_id' => null,
            'type' => AssessmentType::PreTest->value,
            'title' => 'แบบทดสอบก่อนเรียน: '.$course1->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ]);

        Assessment::create([
            'course_id' => $course1->id,
            'module_id' => null,
            'type' => AssessmentType::PostTest->value,
            'title' => 'แบบทดสอบหลังเรียน: '.$course1->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => true,
            'requires_expert_review' => false,
        ]);

        $course2 = Course::create([
            'title' => 'หลักสูตรการพัฒนาการนิเทศการศึกษาเพื่อยกระดับคุณภาพการศึกษา',
            'description' => 'หลักสูตรพัฒนาศักยภาพศึกษานิเทศก์ให้มีความสามารถในการนิเทศการศึกษาอย่างมีประสิทธิภาพ',
            'thumbnail_url' => 'https://picsum.photos/seed/course_2/800/450',
            'duration_hours' => 8.0,
            'passing_score_pct' => 60,
            'has_test' => true,
            'require_review' => true,
            'is_published' => true,
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            CourseImage::create([
                'course_id' => $course2->id,
                'image_url' => 'https://picsum.photos/seed/gallery_2_'.$i.'/800/600',
                'sort_order' => $i * 10,
            ]);
        }

        $modules2 = [
            ['number' => 1, 'title' => 'หลักการและแนวคิดการนิเทศการศึกษา', 'sort_order' => 1],
            ['number' => 2, 'title' => 'กระบวนการนิเทศการสอน', 'sort_order' => 2],
            ['number' => 3, 'title' => 'การวิจัยเพื่อพัฒนาคุณภาพการศึกษา', 'sort_order' => 3],
        ];

        foreach ($modules2 as $m) {
            $module = Module::create([
                'course_id' => $course2->id,
                'module_number' => $m['number'],
                'title' => $m['title'],
                'description' => 'เนื้อหาของ'.$m['title'],
                'thumbnail_url' => 'https://picsum.photos/seed/module_2_'.$m['number'].'/400/300',
                'is_required' => true,
                'requires_expert_review' => $m['number'] === 3,
                'max_test_attempts' => 3,
                'sort_order' => $m['sort_order'],
            ]);

            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Video->value,
                'title' => 'วิดีโอบรรยาย: '.$m['title'],
                'file_url' => 'https://example.com/videos/course2/module'.$m['number'].'.mp4',
                'duration_minutes' => 45.00,
                'sort_order' => 1,
            ]);

            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Link->value,
                'title' => 'แหล่งเรียนรู้เพิ่มเติม',
                'file_url' => 'https://www.obec.go.th',
                'duration_minutes' => null,
                'sort_order' => 2,
            ]);
        }

        Assessment::create([
            'course_id' => $course2->id,
            'module_id' => null,
            'type' => AssessmentType::PreTest->value,
            'title' => 'แบบทดสอบก่อนเรียน: '.$course2->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ]);

        Assessment::create([
            'course_id' => $course2->id,
            'module_id' => null,
            'type' => AssessmentType::PostTest->value,
            'title' => 'แบบทดสอบหลังเรียน: '.$course2->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Mixed->value,
            'is_required_for_cert' => true,
            'requires_expert_review' => true,
        ]);
    }
}
