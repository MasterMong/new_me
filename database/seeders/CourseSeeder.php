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
    /**
     * Topic pools double as the source of module titles/descriptions so
     * content isn't hand-typed per module — each course draws a shuffled
     * subset sized to its module count.
     */
    private const TOPICS_COURSE_1 = [
        'ความรู้พื้นฐานการติดตามและประเมินผล',
        'กระบวนการติดตามและประเมินผลการบริหาร',
        'เครื่องมือและเทคนิคการประเมินผล',
        'การเขียนรายงานการประเมินผล',
        'การนำผลประเมินไปใช้พัฒนาการศึกษา',
        'การวิเคราะห์ข้อมูลเชิงสถิติเพื่อการประเมิน',
        'จรรยาบรรณของนักติดตามและประเมินผล',
    ];

    private const TOPICS_COURSE_2 = [
        'หลักการและแนวคิดการนิเทศการศึกษา',
        'กระบวนการนิเทศการสอน',
        'การวิจัยเพื่อพัฒนาคุณภาพการศึกษา',
        'การสร้างชุมชนแห่งการเรียนรู้ทางวิชาชีพ',
        'การให้ข้อมูลป้อนกลับเชิงสร้างสรรค์',
    ];

    private const DESCRIPTION_FRAMES = [
        'เนื้อหาที่ครอบคลุมเรื่อง:topic พร้อมกรณีศึกษาและแบบฝึกปฏิบัติ',
        'ปูพื้นฐานความเข้าใจเกี่ยวกับ:topic สำหรับการนำไปประยุกต์ใช้จริง',
        'เจาะลึกแนวคิดและแนวปฏิบัติด้าน:topic ที่ใช้ได้จริงในการทำงาน',
        'สรุปหลักการสำคัญของ:topic พร้อมตัวอย่างประกอบ',
    ];

    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Course 1 — sequential, every module gated on the one before it.
        $this->buildCourse($admin, [
            'title' => 'หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน',
            'description' => 'หลักสูตรสำหรับการพัฒนาศักยภาพนักติดตามและประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน เพื่อให้มีความรู้ ความเข้าใจ และทักษะในการติดตาม ประเมินผลอย่างมีประสิทธิภาพ',
            'duration_hours' => 6.0,
            'gallery_count' => 4,
            'is_sequential' => true,
            'module_count' => 5,
            'topics' => self::TOPICS_COURSE_1,
            'review_module_numbers' => [4],
            'seed_prefix' => 'course_1',
            'post_test_grading_mode' => GradingMode::Auto,
            'post_test_requires_expert_review' => false,
        ]);

        // Course 2 — not sequential; module gating instead comes from an
        // explicit ModulePrerequisite (see ModulePrerequisiteSeeder).
        $this->buildCourse($admin, [
            'title' => 'หลักสูตรการพัฒนาการนิเทศการศึกษาเพื่อยกระดับคุณภาพการศึกษา',
            'description' => 'หลักสูตรพัฒนาศักยภาพศึกษานิเทศก์ให้มีความสามารถในการนิเทศการศึกษาอย่างมีประสิทธิภาพ',
            'duration_hours' => 8.0,
            'gallery_count' => 3,
            'is_sequential' => false,
            'module_count' => 3,
            'topics' => self::TOPICS_COURSE_2,
            'review_module_numbers' => [3],
            'seed_prefix' => 'course_2',
            'post_test_grading_mode' => GradingMode::Mixed,
            'post_test_requires_expert_review' => true,
        ]);
    }

    /**
     * @param  array{title: string, description: string, duration_hours: float, gallery_count: int,
     *     is_sequential: bool, module_count: int, topics: array<int, string>, review_module_numbers: array<int, int>,
     *     seed_prefix: string, post_test_grading_mode: GradingMode, post_test_requires_expert_review: bool} $attrs
     */
    private function buildCourse(User $admin, array $attrs): Course
    {
        $course = Course::create([
            'title' => $attrs['title'],
            'description' => $attrs['description'],
            'thumbnail_url' => 'https://picsum.photos/seed/'.$attrs['seed_prefix'].'/800/450',
            'duration_hours' => $attrs['duration_hours'],
            'passing_score_pct' => 60,
            'has_test' => true,
            'require_review' => true,
            'is_published' => true,
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= $attrs['gallery_count']; $i++) {
            CourseImage::create([
                'course_id' => $course->id,
                'image_url' => 'https://picsum.photos/seed/gallery_'.$attrs['seed_prefix'].'_'.$i.'/800/600',
                'sort_order' => $i * 10,
            ]);
        }

        $topics = collect($attrs['topics'])->shuffle()->values();

        for ($number = 1; $number <= $attrs['module_count']; $number++) {
            $topic = $topics[($number - 1) % $topics->count()];
            $requiresExpertReview = in_array($number, $attrs['review_module_numbers'], true);

            $this->buildModule($course, $number, $topic, $requiresExpertReview, $attrs);
        }

        // Course-wide pre-test / post-test — the post-test gates certificate
        // issuance (is_required_for_cert). Curated question banks for these
        // four assessments live in QuestionSeeder.
        Assessment::create([
            'course_id' => $course->id,
            'module_id' => null,
            'type' => AssessmentType::PreTest->value,
            'title' => 'แบบทดสอบก่อนเรียน: '.$course->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ]);

        Assessment::create([
            'course_id' => $course->id,
            'module_id' => null,
            'type' => AssessmentType::PostTest->value,
            'title' => 'แบบทดสอบหลังเรียน: '.$course->title,
            'passing_score_pct' => 60,
            'max_attempts' => 3,
            'grading_mode' => $attrs['post_test_grading_mode']->value,
            'is_required_for_cert' => true,
            'requires_expert_review' => $attrs['post_test_requires_expert_review'],
        ]);

        return $course;
    }

    /**
     * @param  array{is_sequential: bool, seed_prefix: string}  $attrs
     */
    private function buildModule(Course $course, int $number, string $topic, bool $requiresExpertReview, array $attrs): void
    {
        $module = Module::create([
            'course_id' => $course->id,
            'module_number' => $number,
            'title' => "โมดูลที่ {$number}: {$topic}",
            'description' => $this->moduleDescription($topic),
            'thumbnail_url' => 'https://picsum.photos/seed/module_'.$attrs['seed_prefix'].'_'.$number.'/400/300',
            'is_required' => true,
            'requires_expert_review' => $requiresExpertReview,
            'is_sequential' => $attrs['is_sequential'],
            'max_test_attempts' => fake()->randomElement([1, 3, 5]),
            'sort_order' => $number,
        ]);

        $sortOrder = 1;

        // Pre-test opens the module.
        $preTest = $this->createModuleAssessment($course, $module, AssessmentType::PreTest, $topic, $number, isFirst: true);
        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => ContentType::Test->value,
            'assessment_id' => $preTest->id,
            'title' => 'ทดสอบก่อนเรียน: '.$topic,
            'duration_minutes' => $preTest->is_timed ? $preTest->time_limit_minutes : null,
            'sort_order' => $sortOrder++,
        ]);

        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => ContentType::Video->value,
            'title' => 'วิดีโอบรรยาย: '.$topic,
            'file_url' => "https://example.com/videos/{$attrs['seed_prefix']}/module{$number}.mp4",
            'duration_minutes' => fake()->numberBetween(15, 60),
            'sort_order' => $sortOrder++,
        ]);

        // Alternate the supplementary content type per module so both
        // document and link show up across every course, not one each.
        $supplementaryType = $number % 2 === 0 ? ContentType::Link : ContentType::Document;
        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => $supplementaryType->value,
            'title' => $supplementaryType === ContentType::Link ? 'แหล่งเรียนรู้เพิ่มเติม: '.$topic : 'เอกสารประกอบ: '.$topic,
            'file_url' => $supplementaryType === ContentType::Link
                ? 'https://www.obec.go.th'
                : "https://example.com/docs/{$attrs['seed_prefix']}/module{$number}.pdf",
            'duration_minutes' => null,
            'sort_order' => $sortOrder++,
        ]);

        // Post-test caps off the module.
        $postTest = $this->createModuleAssessment($course, $module, AssessmentType::PostTest, $topic, $number, isFirst: false);
        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => ContentType::Test->value,
            'assessment_id' => $postTest->id,
            'title' => 'ทดสอบหลังเรียน: '.$topic,
            'duration_minutes' => $postTest->is_timed ? $postTest->time_limit_minutes : null,
            'sort_order' => $sortOrder++,
        ]);

        // Modules that require expert review also carry a manually-graded
        // assignment — the artifact the assigned expert actually reviews.
        if ($requiresExpertReview) {
            $assignment = Assessment::create([
                'course_id' => $course->id,
                'module_id' => $module->id,
                'type' => AssessmentType::Assignment->value,
                'title' => 'ใบงาน: '.$topic,
                'passing_score_pct' => 60,
                'max_attempts' => fake()->randomElement([1, 2, 3]),
                'grading_mode' => GradingMode::Manual->value,
                'is_required_for_cert' => false,
                'requires_expert_review' => true,
            ]);

            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Test->value,
                'assessment_id' => $assignment->id,
                'title' => 'ส่งใบงาน: '.$topic,
                'duration_minutes' => null,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function createModuleAssessment(Course $course, Module $module, AssessmentType $type, string $topic, int $number, bool $isFirst): Assessment
    {
        // Pre-tests stay auto-graded (diagnostic, not evaluative); post-tests
        // cycle through every grading mode across a course's modules so
        // auto/manual/mixed are all exercised somewhere.
        $gradingMode = $type === AssessmentType::PreTest
            ? GradingMode::Auto
            : GradingMode::cases()[$number % count(GradingMode::cases())];

        $isTimed = $isFirst ? $number % 2 === 0 : $number % 2 === 1;

        return Assessment::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'type' => $type->value,
            'title' => ($type === AssessmentType::PreTest ? 'แบบทดสอบก่อนเรียน: ' : 'แบบทดสอบหลังเรียน: ').$topic,
            'passing_score_pct' => fake()->randomElement([50, 60, 70, 80]),
            'max_attempts' => fake()->randomElement([1, 3, 5, 0]),
            'grading_mode' => $gradingMode->value,
            'is_timed' => $isTimed,
            'time_limit_minutes' => $isTimed ? fake()->randomElement([10, 15, 20, 30]) : null,
            // Module-level tests never gate the certificate — only the two
            // course-wide post-tests do (see buildCourse()) — so they can't
            // drift out of sync with LearnerProgressSeeder's certified scenario.
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ]);
    }

    private function moduleDescription(string $topic): string
    {
        return str_replace(':topic', $topic, fake()->randomElement(self::DESCRIPTION_FRAMES));
    }
}
