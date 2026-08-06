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

        $ongoingCourses = Enrollment::with(['user', 'course.modules.contents.views', 'course.modules.contents.groupAccess'])
            ->where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->get()
            ->map(function ($enrollment) {
                $enrollment->progress_percent = $enrollment->calculateProgressPercent();

                return $enrollment;
            });

        return view('livewire.learner.certificates', [
            'certificates' => $certificates,
            'ongoingCourses' => $ongoingCourses,
            'stats' => [
                'total_earned' => $certificates->count(),
                'in_progress' => $ongoingCourses->count(),
            ],
        ]);
    }
}
