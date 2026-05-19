<?php

namespace App\Livewire\Expert;

use App\Models\Module;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Reports extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Get all modules that require expert review
        $expertModules = Module::where('requires_expert_review', true)->pluck('id');

        // Get learners who have enrollments or attempts in these modules
        $query = User::where('role', 'learner')
            ->whereHas('testAttempts', function ($q) use ($expertModules) {
                $q->whereHas('assessment', function ($q2) use ($expertModules) {
                    $q2->whereIn('module_id', $expertModules);
                });
            });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $learners = $query->withCount([
            'testAttempts as pending_reviews_count' => function ($query) use ($expertModules) {
                $query->where('status', 'pending_review')
                    ->whereHas('assessment', function ($q) use ($expertModules) {
                        $q->whereIn('module_id', $expertModules);
                    });
            },
        ])->paginate(15);

        return view('livewire.expert.reports', [
            'learners' => $learners,
        ]);
    }
}
