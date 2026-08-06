<?php

namespace App\Livewire\Learner;

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\ContentView;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CoursePlayer extends Component
{
    public Course $course;

    public Module $module;

    public ?ModuleContent $activeContent = null;

    public ?Enrollment $enrollment = null;

    public array $expandedModuleIds = [];

    public function mount(Course $course, Module $module, ?ModuleContent $content = null)
    {
        $this->course = $course;
        $this->module = $module;
        $this->expandedModuleIds = [$module->id];

        $this->enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        if (! $this->enrollment) {
            return redirect()->route('courses.show', $course);
        }

        $previousModule = Module::where('course_id', $course->id)
            ->where('sort_order', '<', $module->sort_order)
            ->orderByDesc('sort_order')
            ->with(['assessments.attempts' => fn ($query) => $query->where('user_id', Auth::id())])
            ->first();

        $this->module->load([
            'prerequisites',
            'assessments.attempts' => fn ($query) => $query->where('user_id', Auth::id()),
        ]);

        $coursePreTest = $this->course->assessments()->where('type', 'pre_test')->whereNull('module_id')->first();

        abort_unless($this->isModuleAccessible($this->module, $previousModule, $coursePreTest), 403, 'โมดูลนี้ยังไม่ถูกปลดล็อค');

        // Default to first content if none selected
        if (! $content) {
            $this->activeContent = $this->module->contents()->visibleTo(Auth::user())->orderBy('sort_order')->first();
        } else {
            abort_unless($content->isVisibleTo(Auth::user()), 403);
            $this->activeContent = $content;
        }

        if (! $this->activeContent) {
            return redirect()->route('learn.courses.show', $this->course);
        }
    }

    public function selectContent(ModuleContent $content)
    {
        if ($content->isVisibleTo(Auth::user()) && $this->isContentAccessible($content)) {
            $this->activeContent = $content;
        }
    }

    public function toggleModule(int $moduleId): void
    {
        if (in_array($moduleId, $this->expandedModuleIds, true)) {
            $this->expandedModuleIds = array_values(array_diff($this->expandedModuleIds, [$moduleId]));
        } else {
            $this->expandedModuleIds[] = $moduleId;
        }
    }

    public function updateProgress($durationSec, $lastPositionSec, $isCompleted = false)
    {
        if (! $this->activeContent) {
            return;
        }

        ContentView::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'content_id' => $this->activeContent->id,
            ],
            [
                'watch_duration_sec' => $durationSec,
                'last_position_sec' => $lastPositionSec,
                'is_completed' => $isCompleted,
                'viewed_at' => now(),
            ]
        );

        if ($isCompleted) {
            $this->dispatch('contentCompleted');
            $this->enrollment->issueCertificateIfEligible();
        }
    }

    /**
     * Explicit completion signal for content types with no automatic
     * tracking (document/link) — the learner confirms they've reviewed it.
     */
    public function markComplete(int $contentId): void
    {
        abort_unless($this->activeContent && $this->activeContent->id === $contentId, 403);
        abort_unless(
            in_array($this->activeContent->content_type, [ContentType::Document, ContentType::Link], true),
            403
        );

        ContentView::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'content_id' => $this->activeContent->id,
            ],
            [
                'is_completed' => true,
                'viewed_at' => now(),
            ]
        );

        $this->dispatch('contentCompleted');
        $this->enrollment->issueCertificateIfEligible();
    }

    public function isContentAccessible(ModuleContent $content): bool
    {
        return $content->isAccessibleFor(Auth::user());
    }

    public function render()
    {
        $user = Auth::user();

        $contents = $this->module->contents()
            ->visibleTo($user)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($content) use ($user) {
                $content->is_completed = $content->isCompletedFor($user);
                $content->is_accessible = $this->isContentAccessible($content);

                return $content;
            });

        if ($this->activeContent->content_type === ContentType::Test) {
            $this->activeContent->loadMissing([
                'assessment.attempts' => fn ($query) => $query->where('user_id', $user->id),
                'assessment.questions',
            ]);
        }

        $coursePreTest = $this->course->assessments()->where('type', 'pre_test')->whereNull('module_id')->first();
        $coursePostTest = $this->course->assessments()->where('type', 'post_test')->whereNull('module_id')->first();

        $tree = $this->buildTree($user, $coursePreTest);

        return view('livewire.learner.course-player', [
            'contents' => $contents,
            'tree' => $tree,
            'coursePreTest' => $coursePreTest,
            'coursePostTest' => $coursePostTest,
        ])->title($this->activeContent->title.' - '.$this->course->title);
    }

    /**
     * Whole-course module list (with per-module and per-content-item lock/
     * completion flags) used to populate the tree navigation sidebar.
     */
    protected function buildTree($user, ?Assessment $coursePreTest)
    {
        $courseModules = $this->course->modules()
            ->with([
                'contents' => fn ($query) => $query->orderBy('sort_order'),
                'contents.views',
                'contents.groupAccess',
                'contents.assessment.attempts' => fn ($query) => $query->where('user_id', $user->id),
                'assessments.attempts' => fn ($query) => $query->where('user_id', $user->id),
                'prerequisites',
            ])
            ->get();

        return $courseModules
            ->values()
            ->map(function (Module $module, int $index) use ($courseModules, $user, $coursePreTest) {
                $previousModule = $index > 0 ? $courseModules[$index - 1] : null;
                $module->is_accessible = $this->isModuleAccessible($module, $previousModule, $coursePreTest);
                $module->progress_percent = $module->progressPercentFor($user);
                $module->is_completed = $module->isCompletedFor($user);
                $module->pre_test = $module->assessments->firstWhere('type', AssessmentType::PreTest);
                $module->post_test = $module->assessments->firstWhere('type', AssessmentType::PostTest);

                $module->contents->each(function (ModuleContent $content) use ($user) {
                    $content->is_completed = $content->isCompletedFor($user);
                    $content->is_accessible = $content->isAccessibleFor($user);
                });

                $module->setRelation(
                    'contents',
                    $module->contents->filter(fn (ModuleContent $content) => $content->isVisibleTo($user))->values()
                );

                return $module;
            });
    }

    protected function isModuleAccessible(Module $module, ?Module $previousModule, ?Assessment $coursePreTest): bool
    {
        if ($coursePreTest && ! $coursePreTest->attempts()->where('user_id', Auth::id())->exists()) {
            return false;
        }

        if (! $this->moduleOwnPreTestAttempted($module)) {
            return false;
        }

        foreach ($module->prerequisites as $prerequisite) {
            if ($prerequisite->prerequisite_type->value === 'module') {
                $prereqModule = $prerequisite->prerequisiteModule;
                if (! $prereqModule || ! $prereqModule->isCompletedFor(Auth::user())) {
                    return false;
                }
            } elseif ($prerequisite->prerequisite_type->value === 'assessment') {
                $prereqAssessment = $prerequisite->prerequisiteAssessment;
                if (! $this->isAssessmentPassed($prereqAssessment, $prerequisite->min_score_pct)) {
                    return false;
                }
            }
        }

        if (! $this->previousModuleAssignmentsPassed($previousModule)) {
            return false;
        }

        if (! $this->modulePostTestPassed($previousModule)) {
            return false;
        }

        return true;
    }

    protected function moduleOwnPreTestAttempted(Module $module): bool
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

    protected function isAssessmentPassed(?Assessment $assessment, $minScore): bool
    {
        if (! $assessment) {
            return false;
        }

        $bestAttempt = $assessment->attempts()
            ->where('user_id', Auth::id())
            ->orderByDesc('score_pct')
            ->first();

        return $bestAttempt && $bestAttempt->score_pct >= $minScore;
    }

    protected function extractYoutubeId($url): ?string
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

        return $match[1] ?? null;
    }
}
