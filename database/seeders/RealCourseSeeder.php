<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\User;
use App\Services\DocxTextExtractor;
use App\Services\RealCourseContentParser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Builds the actual ME-Learning course — 9 modules on M&E of basic
 * education management — from the source materials in
 * "ข้อมูลทำระบบ ME-Learning/", replacing the old CourseSeeder's two
 * randomly-generated demo courses.
 *
 * What gets seeded per module, and why:
 * - Video content: one placeholder-video ModuleContent per outline topic
 *   (see RealCourseContentParser::parseModuleOutline()). No recorded lecture
 *   clips exist yet, so these point at a real, playable placeholder video
 *   until the actual footage is uploaded through the admin course-content UI.
 * - Document content: the module's "เอกสารประกอบ" knowledge-sheet .docx,
 *   converted to a real PDF and stored on the public disk — not a fake link.
 * - Pre-test and post-test: the source docs provide one combined MCQ bank
 *   per module ("แบบทดสอบ pre-test และ post-test"), so both assessments are
 *   seeded from the same parsed question set.
 * - A module worksheet (Assignment, manually graded): only for modules 3, 7
 *   and 8, the three the course description names as needing an
 *   expert-graded "ชิ้นงาน" pass. Represented as a single file-upload
 *   question worth the source doc's own stated point threshold — the real
 *   rubric behind that threshold isn't in the source docs, and the expert's
 *   pass/revision verdict is a direct manual choice at review time (see
 *   ReviewSubmission::submitReview()), not computed from score vs a percent,
 *   so passing_score_pct on these three is unused by the app and set to a
 *   fixed 100 rather than guessed.
 */
class RealCourseSeeder extends Seeder
{
    /**
     * No recorded lecture footage exists yet — every video content item
     * points at this real, playable file as a stand-in.
     */
    private const PLACEHOLDER_VIDEO_URL = 'https://phukhieo.ac.th/wp-content/uploads/2022/03/2022-03-02_0-22-24.mp4';

    private const SOURCE_ROOT = 'ข้อมูลทำระบบ ME-Learning';

    private RealCourseContentParser $parser;

    /** @var list<string> */
    private array $warnings = [];

    public function run(): void
    {
        $this->parser = new RealCourseContentParser(new DocxTextExtractor);

        $admin = User::where('role', 'admin')->first();
        $base = base_path(self::SOURCE_ROOT);

        $overview = $this->parser->parseCourseOverview("{$base}/1. คำอธิบายหลหักสูตร/1. รายละเอียดหลักสูตร.docx");

        $course = Course::create([
            'title' => 'หลักสูตรพัฒนาศักยภาพนักติดตาม ประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐาน',
            'description' => 'หลักสูตรพัฒนาศักยภาพนักวิชาการศึกษา บุคลากรในสำนักงานเขตพื้นที่การศึกษาและสถานศึกษา '
                .'ให้มีความรู้ ความเข้าใจ และทักษะในการติดตามและประเมินผลการบริหารและการจัดการศึกษาขั้นพื้นฐานอย่างเป็นระบบ '
                .'ศึกษาด้วยตนเองผ่าน e-Learning ครบ 9 โมดูล',
            'target_audience' => $overview['target_audience'] ?? null,
            'learning_format' => $overview['learning_format'] ?? null,
            'completion_criteria' => $overview['completion_criteria'] ?? null,
            'instructor_team' => $overview['instructor_team'] ?? null,
            'certification_info' => $overview['certification_info'] ?? null,
            'thumbnail_url' => 'https://picsum.photos/seed/me-learning-core/800/450',
            'duration_hours' => 0,
            'passing_score_pct' => 80,
            'has_test' => true,
            'require_review' => true,
            'is_published' => true,
            'created_by' => $admin?->id,
        ]);

        $totalHours = 0;

        for ($n = 1; $n <= 9; $n++) {
            $totalHours += $this->buildModule($course, $base, $n);
        }

        $course->update(['duration_hours' => $totalHours]);

        foreach ($this->warnings as $warning) {
            $this->command?->warn($warning);
        }
    }

    private function buildModule(Course $course, string $base, int $n): int
    {
        $outline = $this->parser->parseModuleOutline("{$base}/3. Outline M1-M9/M{$n}.docx");
        $hasWorksheet = $outline['assignment_min_points'] !== null;

        $module = Module::create([
            'course_id' => $course->id,
            'module_number' => $n,
            'title' => $outline['title'],
            'description' => implode(' ', $outline['objectives']),
            'thumbnail_url' => "https://picsum.photos/seed/me-module-{$n}/400/300",
            'is_required' => true,
            'requires_expert_review' => $hasWorksheet,
            'max_test_attempts' => 3,
            'is_sequential' => true,
            'sort_order' => $n,
        ]);

        $this->createVideoContents($module, $outline);
        $this->createDocumentContent($module, $base, $n, $outline['title']);
        $this->createModuleTests($course, $module, $base, $n, $outline);

        if ($hasWorksheet) {
            $this->createWorksheetAssignment($course, $module, $outline);
        } else {
            $this->createSelfDownloadWorksheet($module, $n, $outline['title']);
        }

        return $outline['hours'];
    }

    /**
     * A placeholder self-download worksheet + answer key for modules with no
     * expert-graded assignment (see course description: "ใบงานแบบผู้เรียน
     * ดาวน์โหลดเอง"). No worksheet template files exist in the source
     * materials to seed real ones from — these are clearly-labeled stand-ins
     * an admin replaces via the module content editor once real worksheets
     * are ready.
     */
    private function createSelfDownloadWorksheet(Module $module, int $n, string $moduleTitle): void
    {
        $worksheetPath = "course-content/module-{$n}-worksheet-placeholder.pdf";
        $answerKeyPath = "course-content/module-{$n}-worksheet-answer-key-placeholder.pdf";

        if (! Storage::disk('public')->exists($worksheetPath)) {
            Storage::disk('public')->put(
                $worksheetPath,
                Pdf::loadHTML($this->placeholderWorksheetHtml($moduleTitle, isAnswerKey: false))->output()
            );
        }

        if (! Storage::disk('public')->exists($answerKeyPath)) {
            Storage::disk('public')->put(
                $answerKeyPath,
                Pdf::loadHTML($this->placeholderWorksheetHtml($moduleTitle, isAnswerKey: true))->output()
            );
        }

        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => ContentType::Worksheet->value,
            'title' => 'ใบงาน: '.$moduleTitle,
            'file_url' => Storage::disk('public')->url($worksheetPath),
            'answer_key_url' => Storage::disk('public')->url($answerKeyPath),
            'duration_minutes' => null,
            'sort_order' => 999,
        ]);
    }

    private function placeholderWorksheetHtml(string $moduleTitle, bool $isAnswerKey): string
    {
        $heading = $isAnswerKey ? 'เฉลยใบงาน (ตัวอย่าง)' : 'ใบงาน (ตัวอย่าง)';
        $body = $isAnswerKey
            ? 'นี่คือไฟล์เฉลยตัวอย่าง — รอไฟล์เฉลยจริงจากทีมผู้สอน'
            : 'นี่คือไฟล์ใบงานตัวอย่าง — รอไฟล์ใบงานจริงจากทีมผู้สอน';

        return <<<HTML
            <html>
                <head>
                    <style>
                        body { font-family: 'DejaVu Sans', sans-serif; padding: 60px; color: #14213d; }
                        h1 { color: #003e74; }
                        p { font-size: 14px; color: #545f77; }
                    </style>
                </head>
                <body>
                    <h1>{$heading}</h1>
                    <h2>{$moduleTitle}</h2>
                    <p>{$body}</p>
                </body>
            </html>
        HTML;
    }

    /**
     * @param  array{topics: list<string>, hours: int}  $outline
     */
    private function createVideoContents(Module $module, array $outline): void
    {
        $topics = $outline['topics'];

        if ($topics === []) {
            return;
        }

        $minutesPerTopic = intdiv($outline['hours'] * 60, count($topics));

        foreach ($topics as $i => $topic) {
            ModuleContent::create([
                'module_id' => $module->id,
                'content_type' => ContentType::Video->value,
                'title' => $topic,
                'file_url' => self::PLACEHOLDER_VIDEO_URL,
                'duration_minutes' => $minutesPerTopic,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function createDocumentContent(Module $module, string $base, int $n, string $moduleTitle): void
    {
        $matches = glob("{$base}/5. เอกสารประกอบ/M{$n}/*.docx");

        if ($matches === false || $matches === []) {
            $this->warnings[] = "M{$n}: no supporting document found under \"5. เอกสารประกอบ/M{$n}/\" — skipped its document content item.";

            return;
        }

        $pdfUrl = $this->convertToStoredPdf($matches[0], $n);

        if ($pdfUrl === null) {
            return;
        }

        ModuleContent::create([
            'module_id' => $module->id,
            'content_type' => ContentType::Document->value,
            'title' => 'เอกสารประกอบการเรียน: '.$moduleTitle,
            'file_url' => $pdfUrl,
            'duration_minutes' => null,
            'sort_order' => 1000,
        ]);
    }

    /**
     * Convert the module's knowledge-sheet .docx to a real PDF via a
     * headless LibreOffice and store it on the public disk. Returns null
     * (with a warning recorded) if the `soffice` binary isn't available in
     * this environment, rather than failing the whole import over one
     * missing document.
     */
    private function convertToStoredPdf(string $docxPath, int $n): ?string
    {
        $tmpDir = storage_path('app/tmp-course-import');

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $result = Process::timeout(120)->run([
            'soffice', '--headless', '--convert-to', 'pdf', '--outdir', $tmpDir, $docxPath,
        ]);

        if (! $result->successful()) {
            $this->warnings[] = "M{$n}: soffice failed to convert the supporting document to PDF — skipped its document content item. ({$result->errorOutput()})";

            return null;
        }

        $generatedPdf = $tmpDir.'/'.pathinfo($docxPath, PATHINFO_FILENAME).'.pdf';

        if (! is_file($generatedPdf)) {
            $this->warnings[] = "M{$n}: expected converted PDF not found at {$generatedPdf} — skipped its document content item.";

            return null;
        }

        $storedPath = "course-content/module-{$n}-เอกสารประกอบ.pdf";
        Storage::disk('public')->put($storedPath, file_get_contents($generatedPdf));
        unlink($generatedPdf);

        return Storage::disk('public')->url($storedPath);
    }

    /**
     * @param  array{title: string, passing_score_pct: int}  $outline
     */
    private function createModuleTests(Course $course, Module $module, string $base, int $n, array $outline): void
    {
        $testPath = "{$base}/4. Pre-Post Test/M{$n} แบบทดสอบ.docx";
        $qWarnings = [];
        $questions = $this->parser->parseQuestions($testPath, $qWarnings);

        foreach ($qWarnings as $warning) {
            $this->warnings[] = "M{$n}: {$warning}";
        }

        $preTest = Assessment::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'type' => AssessmentType::PreTest->value,
            'title' => 'แบบทดสอบก่อนเรียน: '.$outline['title'],
            'passing_score_pct' => $outline['passing_score_pct'],
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            // Pre-tests are diagnostic only — never required for certification.
            'is_required_for_cert' => false,
            'requires_expert_review' => false,
        ]);
        $this->createMultipleChoiceQuestions($preTest, $questions);

        $postTest = Assessment::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'type' => AssessmentType::PostTest->value,
            'title' => 'แบบทดสอบหลังเรียน: '.$outline['title'],
            'passing_score_pct' => $outline['passing_score_pct'],
            'max_attempts' => 3,
            'grading_mode' => GradingMode::Auto->value,
            'is_required_for_cert' => true,
            'requires_expert_review' => false,
        ]);
        $this->createMultipleChoiceQuestions($postTest, $questions);
    }

    /**
     * @param  list<array{text: string, choices: array{0:string,1:string,2:string,3:string}, correct: int}>  $questions
     */
    private function createMultipleChoiceQuestions(Assessment $assessment, array $questions): void
    {
        foreach ($questions as $sortOrder => $q) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_type' => QuestionType::MultipleChoice->value,
                'question_text' => $q['text'],
                'points' => 1,
                'grading_mode' => GradingMode::Auto->value,
                'sort_order' => $sortOrder + 1,
            ]);

            foreach ($q['choices'] as $choiceOrder => $choiceText) {
                QuestionChoice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceText,
                    'is_correct' => $choiceOrder === $q['correct'],
                    'sort_order' => $choiceOrder + 1,
                ]);
            }
        }
    }

    /**
     * @param  array{title: string, assignment_min_points: int}  $outline
     */
    private function createWorksheetAssignment(Course $course, Module $module, array $outline): void
    {
        $assignment = Assessment::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'type' => AssessmentType::Assignment->value,
            'title' => 'ใบงาน: '.$outline['title'],
            // Unused for manually-graded assignments — see class docblock.
            'passing_score_pct' => 100,
            'max_attempts' => 1,
            'grading_mode' => GradingMode::Manual->value,
            'is_required_for_cert' => true,
            'requires_expert_review' => true,
        ]);

        Question::create([
            'assessment_id' => $assignment->id,
            'question_type' => QuestionType::FileUpload->value,
            'question_text' => 'แนบไฟล์ใบงานของโมดูลนี้ (ผ่านเกณฑ์การให้คะแนนจากผู้เชี่ยวชาญ ตั้งแต่ '
                .$outline['assignment_min_points'].' คะแนนขึ้นไป)',
            'points' => $outline['assignment_min_points'],
            'grading_mode' => GradingMode::Manual->value,
            'sort_order' => 1,
        ]);
    }
}
