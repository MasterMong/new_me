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

        $enrollments = Enrollment::with(['user', 'course.modules.contents.views', 'course.modules.contents.groupAccess'])
            ->where('user_id', $user->id)
            ->latest('enrolled_at')
            ->get()
            ->map(function ($enrollment) {
                $enrollment->progress_percent = $enrollment->calculateProgressPercent();

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
