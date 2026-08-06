<?php

namespace App\Livewire\Expert;

use App\Models\Module;
use App\Models\TestAttempt;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $expertModules = Module::where('requires_expert_review', true)
            ->with('expertAssignments')
            ->withCount([
                'assessments as pending_reviews_count' => function ($query) {
                    $query->join('test_attempts', 'test_attempts.assessment_id', '=', 'assessments.id')
                        ->where('test_attempts.status', 'pending_review');
                },
                'assessments as overdue_reviews_count' => function ($query) {
                    $query->join('test_attempts', 'test_attempts.assessment_id', '=', 'assessments.id')
                        ->where('test_attempts.status', 'pending_review')
                        ->where('test_attempts.submitted_at', '<=', now()->subDays(3));
                },
            ])
            ->get()
            ->filter(fn (Module $module) => $module->isAssignedTo(auth()->user()))
            ->values();

        $totalPending = $expertModules->sum('pending_reviews_count');
        $totalOverdue = $expertModules->sum('overdue_reviews_count');

        $totalCompleted = TestAttempt::whereHas('expertReview', function ($query) {
            $query->where('expert_id', auth()->id());
        })->count();

        return view('livewire.expert.dashboard', [
            'modules' => $expertModules,
            'totalPending' => $totalPending,
            'totalOverdue' => $totalOverdue,
            'totalCompleted' => $totalCompleted,
        ]);
    }
}
