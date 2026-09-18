<?php

namespace App\Livewire\Learner;

use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('หน้าหลักผู้เรียน')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $enrollments = Enrollment::with([
            'user',
            'course.modules.contents.views',
            'course.modules.contents.groupAccess',
            'course.modules.assessments.attempts',
        ])
            ->where('user_id', $user->id)
            ->get()
            ->map(function (Enrollment $enrollment) use ($user) {
                $enrollment->progress_percent = $enrollment->calculateProgressPercent();
                $enrollment->completed_module_count = $enrollment->course->modules
                    ->filter(fn ($module) => $module->isCompletedFor($user))
                    ->count();
                $enrollment->total_module_count = $enrollment->course->modules->count();
                $enrollment->last_activity_at = $enrollment->course->modules
                    ->flatMap(fn ($module) => $module->contents)
                    ->flatMap(fn ($content) => $content->views)
                    ->where('user_id', $user->id)
                    ->max('viewed_at');

                return $enrollment;
            });

        $activeEnrollment = $enrollments
            ->filter(fn (Enrollment $e) => $e->progress_percent > 0 && $e->progress_percent < 100)
            ->sortByDesc('last_activity_at')
            ->first();

        $otherEnrollments = $activeEnrollment
            ? $enrollments->reject(fn (Enrollment $e) => $e->id === $activeEnrollment->id)
            : $enrollments;

        return view('livewire.learner.dashboard', [
            'activeEnrollment' => $activeEnrollment,
            'otherEnrollments' => $otherEnrollments,
            'hasEnrollments' => $enrollments->isNotEmpty(),
        ]);
    }
}
