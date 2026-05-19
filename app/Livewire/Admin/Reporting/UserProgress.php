<?php

namespace App\Livewire\Admin\Reporting;

use App\Enums\UserRole;
use App\Models\LearnerGroup;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserProgress extends Component
{
    use WithPagination;

    public string $search = '';

    public string $groupId = '';

    public ?int $selectedUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectUser(?int $id): void
    {
        $this->selectedUserId = $id;
        $this->resetPage();
    }

    public function render()
    {
        if ($this->selectedUserId) {
            return $this->renderUserDetails();
        }

        $users = User::query()
            ->where('role', UserRole::Learner)
            ->withCount(['enrollments'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->groupId, function ($q) {
                $q->whereHas('groupMemberships', fn ($sq) => $sq->where('group_id', $this->groupId));
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.reporting.user-progress', [
            'users' => $users,
            'groups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'รายงานความก้าวหน้าตามรายบุคคล']);
    }

    protected function renderUserDetails()
    {
        $user = User::with(['enrollments.course', 'groupMemberships.group'])->findOrFail($this->selectedUserId);

        return view('livewire.admin.reporting.user-details', [
            'user' => $user,
        ])->layout('layouts.app', ['title' => 'รายละเอียดผู้เรียน: '.$user->fullName()]);
    }
}
