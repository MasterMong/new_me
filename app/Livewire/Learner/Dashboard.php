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
        $enrollments = Enrollment::with(['user', 'course.modules.contents.views', 'course.modules.contents.groupAccess'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($enrollment) {
                $enrollment->progress_percent = $enrollment->calculateProgressPercent();

                return $enrollment;
            });

        return view('livewire.learner.dashboard', [
            'enrollments' => $enrollments,
        ]);
    }
}
