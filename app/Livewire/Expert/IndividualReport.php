<?php

namespace App\Livewire\Expert;

use App\Enums\UserRole;
use App\Models\Module;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class IndividualReport extends Component
{
    public User $user;

    public function mount(User $user)
    {
        abort_unless($user->role === UserRole::Learner, 404);
        $this->user = $user;
    }

    public function render()
    {
        // Get all modules that require expert review
        $expertModules = Module::where('requires_expert_review', true)->get();
        $moduleIds = $expertModules->pluck('id');

        // Get test attempts for this user for the expert modules
        $attempts = TestAttempt::with(['assessment.module', 'expertReview'])
            ->where('user_id', $this->user->id)
            ->whereHas('assessment', function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.expert.individual-report', [
            'modules' => $expertModules,
            'attempts' => $attempts,
        ]);
    }
}
