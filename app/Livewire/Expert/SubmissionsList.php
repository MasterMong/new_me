<?php

namespace App\Livewire\Expert;

use App\Models\Module;
use App\Models\TestAttempt;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SubmissionsList extends Component
{
    use WithPagination;

    public Module $module;

    public string $statusFilter = '';

    public string $search = '';

    public function mount(Module $module)
    {
        $this->module = $module;
        // Ensure module requires expert review
        abort_unless($module->requires_expert_review, 403, 'This module does not require expert review.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $assessments = $this->module->assessments()->pluck('id');

        $query = TestAttempt::with(['user', 'expertReview'])
            ->whereIn('assessment_id', $assessments)
            // Only fetch attempts that are submitted or already reviewed by expert
            ->whereIn('status', ['pending_review', 'passed', 'failed', 'revision_needed']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $submissions = $query->orderBy('submitted_at', 'desc')->paginate(15);

        return view('livewire.expert.submissions-list', [
            'submissions' => $submissions,
        ]);
    }
}
