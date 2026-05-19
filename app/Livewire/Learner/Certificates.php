<?php

namespace App\Livewire\Learner;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('เกียรติบัตรและความสำเร็จ')]
class Certificates extends Component
{
    public function render()
    {
        $certificates = Certificate::with('course')
            ->where('user_id', Auth::id())
            ->orderByDesc('issued_date')
            ->get();

        $ongoingCourses = Enrollment::with(['course.modules.contents.views'])
            ->where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->get()
            ->map(function ($enrollment) {
                $course = $enrollment->course;
                $totalContents = $course->modules->sum(fn ($module) => $module->contents->count());

                $completedContents = $course->modules->sum(function ($module) {
                    return $module->contents->filter(function ($content) {
                        return $content->views->where('user_id', Auth::id())->where('is_completed', true)->isNotEmpty();
                    })->count();
                });

                $enrollment->progress_percent = $totalContents > 0
                    ? round(($completedContents / $totalContents) * 100)
                    : 0;

                return $enrollment;
            });

        return view('livewire.learner.certificates', [
            'certificates' => $certificates,
            'ongoingCourses' => $ongoingCourses,
            'stats' => [
                'total_earned' => $certificates->count(),
                'in_progress' => $ongoingCourses->count(),
                'total_courses' => Enrollment::where('user_id', Auth::id())->count(),
            ],
        ]);
    }
}
