<?php

namespace App\Livewire\Learner;

use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ความก้าวหน้าของฉัน')]
class MyProgress extends Component
{
    public function render()
    {
        $user = Auth::user();

        $enrollments = Enrollment::with(['course.modules.contents.views'])
            ->where('user_id', $user->id)
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

        // Statistics
        $stats = [
            'total_enrolled' => $enrollments->count(),
            'completed' => $enrollments->where('progress_percent', 100)->count(),
            'in_progress' => $enrollments->where('progress_percent', '>', 0)->where('progress_percent', '<', 100)->count(),
            'avg_progress' => $enrollments->avg('progress_percent') ?? 0,
        ];

        return view('livewire.learner.my-progress', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            'user' => $user,
        ]);
    }
}
