<?php

namespace App\Livewire\Learner;

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Models\Course;
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

        $modules = $courseModules
            ->values()
            ->map(function ($module, $index) use ($courseModules) {
                $previousModule = $index > 0 ? $courseModules[$index - 1] : null;
                $module->is_accessible = $this->checkModuleAccessibility($module, $previousModule);
                $module->progress_percent = $this->calculateModuleProgress($module);
                $module->is_completed = $module->progress_percent === 100;

                return $module;
            });

        $preTest = $this->course->assessments()->where('type', 'pre_test')->first();
        $postTest = $this->course->assessments()->where('type', 'post_test')->first();

        return view('livewire.learner.course-path', [
            'modules' => $modules,
            'preTest' => $preTest,
            'postTest' => $postTest,
        ])->title($this->course->title);
    }

    protected function checkModuleAccessibility($module, ?Module $previousModule): bool
    {
        // Pre-test must be completed if it exists
        $preTest = $this->course->assessments()->where('type', 'pre_test')->first();
        if ($preTest && ! $preTest->attempts()->where('user_id', Auth::id())->exists()) {
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

        return true;
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
        $visibleContents = $module->contents->filter(fn ($content) => $content->isVisibleTo(Auth::user()));

        $totalContents = $visibleContents->count();
        if ($totalContents === 0) {
            return 100;
        }

        $completedContents = $visibleContents->filter(function ($content) {
            return $content->views->where('user_id', Auth::id())->where('is_completed', true)->isNotEmpty();
        })->count();

        return round(($completedContents / $totalContents) * 100);
    }

    protected function isModuleCompleted($module): bool
    {
        return $this->calculateModuleProgress($module) === 100;
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
