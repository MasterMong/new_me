<?php

namespace App\Livewire\Admin\Groups;

use App\Models\LearnerGroup;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showCreateModal = false;

    public string $newGroupName = '';

    public string $newGroupDescription = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createGroup(): void
    {
        $this->validate([
            'newGroupName' => 'required|string|max:255',
            'newGroupDescription' => 'nullable|string|max:1000',
        ]);

        LearnerGroup::create([
            'name' => $this->newGroupName,
            'description' => $this->newGroupDescription ?: null,
            'is_active' => true,
        ]);

        $this->reset(['newGroupName', 'newGroupDescription', 'showCreateModal']);
    }

    public function toggleActive(int $groupId): void
    {
        $group = LearnerGroup::findOrFail($groupId);
        $group->update(['is_active' => ! $group->is_active]);
    }

    public function deleteGroup(int $groupId): void
    {
        LearnerGroup::findOrFail($groupId)->delete();
    }

    public function render()
    {
        $groups = LearnerGroup::query()
            ->withCount('users')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.groups.index', ['groups' => $groups])
            ->layout('layouts.app', ['title' => 'จัดการกลุ่มผู้เรียน']);
    }
}
