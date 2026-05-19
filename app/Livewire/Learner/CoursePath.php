<?php

namespace App\Livewire\Learner;

use App\Models\Course;
use App\Models\Enrollment;
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
        $modules = $this->course->modules()
            ->with(['prerequisites', 'contents.views', 'assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->map(function ($module) {
                $module->is_accessible = $this->checkModuleAccessibility($module);
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

    protected function checkModuleAccessibility($module): bool
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

        return true;
    }

    protected function calculateModuleProgress($module): int
    {
        $totalContents = $module->contents->count();
        if ($totalContents === 0) {
            return 100;
        }

        $completedContents = $module->contents->filter(function ($content) {
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
