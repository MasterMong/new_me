<?php

namespace App\Livewire\Admin\Reporting;

use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Component;

class ExpertTurnaround extends Component
{
    public function render()
    {
        $experts = User::where('role', UserRole::Expert->value)
            ->with('expertReviews.attempt')
            ->get()
            ->map(function (User $expert) {
                $completedReviews = $expert->expertReviews;

                $turnaroundHours = $completedReviews
                    ->filter(fn ($review) => $review->attempt?->submitted_at && $review->reviewed_at)
                    ->map(fn ($review) => $review->attempt->submitted_at->diffInHours($review->reviewed_at));

                $assignedModuleIds = $expert->assignedModules()->pluck('modules.id');

                $pendingCount = TestAttempt::where('status', TestAttemptStatus::PendingReview->value)
                    ->whereHas('assessment', fn ($q) => $q->whereIn('module_id', $assignedModuleIds))
                    ->count();

                return [
                    'expert' => $expert,
                    'pending_count' => $pendingCount,
                    'completed_count' => $completedReviews->count(),
                    'avg_turnaround_hours' => $turnaroundHours->isNotEmpty() ? round($turnaroundHours->avg(), 1) : null,
                ];
            })
            ->sortByDesc('pending_count')
            ->values();

        $openQueueCount = TestAttempt::where('status', TestAttemptStatus::PendingReview->value)
            ->whereHas('assessment.module', fn ($q) => $q->whereDoesntHave('expertAssignments'))
            ->count();

        return view('livewire.admin.reporting.expert-turnaround', [
            'experts' => $experts,
            'openQueueCount' => $openQueueCount,
        ])->layout('layouts.app', ['title' => 'ความเร็วในการตรวจของผู้เชี่ยวชาญ']);
    }
}
