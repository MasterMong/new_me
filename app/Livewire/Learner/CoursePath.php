<?php

namespace App\Livewire\Learner;

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CoursePath extends Component
{
    public Course $course;

    public ?Enrollment $enrollment = null;

    public function mount(Course $course)
    {
        $this->course = $course;
        $this->enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        if (! $this->enrollment) {
            return redirect()->route('courses.show', $course);
        }
    }

    public function getTitleProperty()
    {
        return $this->course->title;
    }

    public function render()
    {
        $courseModules = $this->course->modules()
            ->with(['prerequisites', 'contents.views', 'contents.groupAccess', 'assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get();

        $preTest = $this->course->assessments()->where('type', 'pre_test')->whereNull('module_id')->first();
        $postTest = $this->course->assessments()->where('type', 'post_test')->whereNull('module_id')->first();

        $modules = $courseModules
            ->values()
            ->map(function ($module, $index) use ($courseModules, $preTest) {
                $previousModule = $index > 0 ? $courseModules[$index - 1] : null;
                $module->is_accessible = $this->checkModuleAccessibility($module, $previousModule, $preTest);
                $module->progress_percent = $this->calculateModuleProgress($module);
                $module->is_completed = $this->isModuleCompleted($module);
                $module->pre_test = $module->assessments->firstWhere('type', AssessmentType::PreTest);
                $module->post_test = $module->assessments->firstWhere('type', AssessmentType::PostTest);
                $module->lock_reason = $module->is_accessible
                    ? null
                    : $this->moduleLockReason($module, $previousModule, $preTest);

                if ($module->is_accessible && ! $module->is_completed) {
                    $module->next_action = $this->moduleNextAction($module);
                }

                return $module;
            });

        $completedModuleCount = $modules->filter(fn ($m) => $m->is_completed)->count();

        return view('livewire.learner.course-path', [
            'modules' => $modules,
            'preTest' => $preTest,
            'postTest' => $postTest,
            'completedModuleCount' => $completedModuleCount,
            'nextStep' => $this->resolveNextStep($modules, $preTest, $postTest),
        ])->title($this->course->title);
    }

    /**
     * The single most relevant "what do I do right now" item for this
     * course: the course pre-test, the first accessible-but-incomplete
     * module, the course post-test, or a post-completion action (review
     * the course, or nothing left — certificate already issued).
     *
     * @return array{type: string, title: string, subtitle: string, href: string, cta: string}|null
     */
    protected function resolveNextStep($modules, ?Assessment $preTest, ?Assessment $postTest): ?array
    {
        if ($preTest && ! $preTest->attempts()->where('user_id', Auth::id())->exists()) {
            return [
                'type' => 'pretest',
                'key' => 'assessment:'.$preTest->id,
                'title' => $preTest->title,
                'subtitle' => 'แบบทดสอบก่อนเรียนของหลักสูตร',
                'href' => route('learn.assessments.show', $preTest),
                'cta' => 'เริ่มทำแบบทดสอบ',
            ];
        }

        $activeModule = $modules->first(fn ($m) => $m->is_accessible && ! $m->is_completed);

        if ($activeModule) {
            return [
                'type' => 'module',
                'key' => 'module:'.$activeModule->id,
                'title' => 'โมดูล '.$activeModule->module_number.': '.$activeModule->title,
                'subtitle' => $activeModule->next_action['subtitle'],
                'href' => $activeModule->next_action['href'],
                'cta' => $activeModule->next_action['label'],
            ];
        }

        if ($postTest) {
            $postTestPassed = $postTest->attempts()
                ->where('user_id', Auth::id())
                ->where('status', TestAttemptStatus::Passed->value)
                ->exists();

            if (! $postTestPassed) {
                return [
                    'type' => 'posttest',
                    'key' => 'assessment:'.$postTest->id,
                    'subtitle' => 'แบบทดสอบหลังเรียนสรุปหลักสูตร',
                    'title' => $postTest->title,
                    'href' => route('learn.assessments.show', $postTest),
                    'cta' => 'เริ่มทำแบบทดสอบ',
                ];
            }
        }

        $hasReviewed = CourseReview::where('user_id', Auth::id())->where('course_id', $this->course->id)->exists();

        if (! $hasReviewed) {
            return [
                'type' => 'review',
                'title' => 'ให้คะแนนและรีวิวหลักสูตรนี้',
                'subtitle' => 'ขั้นตอนสุดท้ายก่อนรับเกียรติบัตร',
                'href' => route('learn.courses.review', $this->course),
                'cta' => 'ให้คะแนนหลักสูตร',
            ];
        }

        $hasCertificate = Certificate::where('user_id', Auth::id())->where('course_id', $this->course->id)->exists();

        if ($hasCertificate) {
            return [
                'type' => 'done',
                'title' => 'คุณเรียนจบหลักสูตรนี้เรียบร้อยแล้ว',
                'subtitle' => 'ดาวน์โหลดเกียรติบัตรของคุณได้แล้ว',
                'href' => route('learn.certificates.index'),
                'cta' => 'ดูเกียรติบัตร',
            ];
        }

        return null;
    }

    /**
     * The specific next thing to do inside an accessible, not-yet-completed
     * module: its own pre-test, its content, or its own post-test.
     *
     * @return array{label: string, subtitle: string, href: string}
     */
    protected function moduleNextAction(Module $module): array
    {
        if ($module->pre_test && $module->pre_test->attempts->isEmpty()) {
            return [
                'label' => 'ทำแบบทดสอบก่อนเรียน',
                'subtitle' => 'แบบทดสอบก่อนเรียนของโมดูลนี้',
                'href' => route('learn.assessments.show', $module->pre_test),
            ];
        }

        if ($module->progress_percent < 100) {
            return [
                'label' => $module->progress_percent > 0 ? 'เรียนต่อ' : 'เริ่มบทเรียน',
                'subtitle' => $this->moduleContentSummary($module),
                'href' => route('learn.courses.play', ['course' => $this->course->id, 'module' => $module->id]),
            ];
        }

        return [
            'label' => 'ทำแบบทดสอบหลังเรียน',
            'subtitle' => 'แบบทดสอบหลังเรียนของโมดูลนี้',
            'href' => route('learn.assessments.show', $module->post_test),
        ];
    }

    /**
     * A short "วิดีโอ 1 · เอกสาร 2" summary of a module's visible content,
     * grouped by type in the order content actually appears.
     */
    protected function moduleContentSummary(Module $module): string
    {
        return $module->contents
            ->filter(fn ($content) => $content->isVisibleTo(Auth::user()))
            ->groupBy('content_type')
            ->map(fn ($group, $type) => ContentType::from($type)->label().' '.$group->count())
            ->implode(' · ');
    }

    protected function checkModuleAccessibility($module, ?Module $previousModule, $coursePreTest): bool
    {
        // Course-wide pre-test must be completed if it exists
        if ($coursePreTest && ! $coursePreTest->attempts()->where('user_id', Auth::id())->exists()) {
            return false;
        }

        // This module's own pre-test must be attempted before its content unlocks
        if (! $this->moduleOwnPreTestAttempted($module)) {
            return false;
        }

        // Check prerequisites
        foreach ($module->prerequisites as $prerequisite) {
            if ($prerequisite->prerequisite_type->value === 'module') {
                $prereqModule = $prerequisite->prerequisiteModule;
                if (! $this->isModuleCompleted($prereqModule)) {
                    return false;
                }
            } elseif ($prerequisite->prerequisite_type->value === 'assessment') {
                $prereqAssessment = $prerequisite->prerequisiteAssessment;
                if (! $this->isAssessmentPassed($prereqAssessment, $prerequisite->min_score_pct)) {
                    return false;
                }
            }
        }

        // If the previous module has an assignment/worksheet, it must be passed
        // before this module unlocks — even without an explicit prerequisite record.
        if (! $this->previousModuleAssignmentsPassed($previousModule)) {
            return false;
        }

        // Same for the previous module's own post-test, if it has one.
        if (! $this->modulePostTestPassed($previousModule)) {
            return false;
        }

        return true;
    }

    /**
     * The specific reason a locked module is locked, mirroring
     * checkModuleAccessibility()'s checks in the same order so the message
     * always matches the actual gate that's blocking access.
     */
    protected function moduleLockReason(Module $module, ?Module $previousModule, $coursePreTest): ?string
    {
        if ($coursePreTest && ! $coursePreTest->attempts()->where('user_id', Auth::id())->exists()) {
            return 'ต้องทำแบบทดสอบก่อนเรียนของหลักสูตรก่อน';
        }

        if (! $this->moduleOwnPreTestAttempted($module)) {
            return 'ต้องทำแบบทดสอบก่อนเรียนของโมดูลนี้ก่อน';
        }

        foreach ($module->prerequisites as $prerequisite) {
            if ($prerequisite->prerequisite_type->value === 'module') {
                $prereqModule = $prerequisite->prerequisiteModule;
                if (! $this->isModuleCompleted($prereqModule)) {
                    return "ต้องเรียนโมดูลที่ {$prereqModule->module_number} ให้จบก่อน";
                }
            } elseif ($prerequisite->prerequisite_type->value === 'assessment') {
                $prereqAssessment = $prerequisite->prerequisiteAssessment;
                if (! $this->isAssessmentPassed($prereqAssessment, $prerequisite->min_score_pct)) {
                    return "ต้องสอบผ่าน \"{$prereqAssessment->title}\" ก่อน";
                }
            }
        }

        if (! $this->previousModuleAssignmentsPassed($previousModule)) {
            return "ต้องผ่านงานที่ได้รับมอบหมายของโมดูลที่ {$previousModule->module_number} ก่อน";
        }

        if (! $this->modulePostTestPassed($previousModule)) {
            return "ต้องสอบผ่านแบบทดสอบหลังเรียนของโมดูลที่ {$previousModule->module_number} ก่อน";
        }

        return null;
    }

    protected function moduleOwnPreTestAttempted($module): bool
    {
        $preTest = $module->assessments->firstWhere('type', AssessmentType::PreTest);

        if (! $preTest) {
            return true;
        }

        return $preTest->attempts->isNotEmpty();
    }

    protected function modulePostTestPassed(?Module $module): bool
    {
        return ! $module || $module->postTestPassedFor(Auth::user());
    }

    protected function previousModuleAssignmentsPassed(?Module $previousModule): bool
    {
        if (! $previousModule) {
            return true;
        }

        $assignments = $previousModule->assessments->filter(
            fn ($assessment) => $assessment->type === AssessmentType::Assignment
        );

        foreach ($assignments as $assignment) {
            $passed = $assignment->attempts->contains(
                fn ($attempt) => $attempt->status === TestAttemptStatus::Passed
            );

            if (! $passed) {
                return false;
            }
        }

        return true;
    }

    protected function calculateModuleProgress($module): int
    {
        return $module->progressPercentFor(Auth::user());
    }

    protected function isModuleCompleted($module): bool
    {
        return $module->isCompletedFor(Auth::user());
    }

    protected function isAssessmentPassed($assessment, $minScore): bool
    {
        $bestAttempt = $assessment->attempts()
            ->where('user_id', Auth::id())
            ->orderByDesc('score_pct')
            ->first();

        return $bestAttempt && $bestAttempt->score_pct >= $minScore;
    }
}
