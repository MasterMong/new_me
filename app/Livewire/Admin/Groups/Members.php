<?php

namespace App\Livewire\Admin\Groups;

use App\Models\LearnerGroup;
use App\Models\User;
use App\Models\UserGroupMembership;
use Livewire\Component;
use Livewire\WithPagination;

class Members extends Component
{
    use WithPagination;

    public LearnerGroup $group;

    public string $search = '';

    public bool $showAddModal = false;

    public string $userSearch = '';

    public array $selectedUserIds = [];

    public function mount(LearnerGroup $group): void
    {
        $this->group = $group;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingUserSearch(): void
    {
        $this->selectedUserIds = [];
    }

    public function updatedShowAddModal(bool $value): void
    {
        if (! $value) {
            $this->userSearch = '';
            $this->selectedUserIds = [];
        }
    }

    public function addMembers(): void
    {
        if (empty($this->selectedUserIds)) {
            return;
        }

        foreach ($this->selectedUserIds as $userId) {
            UserGroupMembership::firstOrCreate(
                ['user_id' => (int) $userId, 'group_id' => $this->group->id],
                ['assigned_at' => now(), 'assigned_by' => auth()->id()]
            );
        }

        $this->selectedUserIds = [];
        $this->userSearch = '';
        $this->showAddModal = false;
    }

    public function removeMember(int $membershipId): void
    {
        UserGroupMembership::findOrFail($membershipId)->delete();
    }

    public function render()
    {
        $members = $this->group->memberships()
            ->with(['user.position', 'assignedBy'])
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($q) => $q
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->latest()
            ->paginate(15);

        $existingUserIds = $this->group->memberships()->pluck('user_id');

        $availableUsers = User::whereNotIn('id', $existingUserIds)
            ->where('is_active', true)
            ->when($this->userSearch, fn ($q) => $q
                ->where('first_name', 'like', "%{$this->userSearch}%")
                ->orWhere('last_name', 'like', "%{$this->userSearch}%")
                ->orWhere('email', 'like', "%{$this->userSearch}%")
            )
            ->orderBy('first_name')
            ->limit(20)
            ->get();

        return view('livewire.admin.groups.members', compact('members', 'availableUsers'))
            ->layout('layouts.app', ['title' => 'สมาชิก: '.$this->group->name]);
    }
}
