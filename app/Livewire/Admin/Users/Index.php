<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        $users = User::query()
            ->with(['position', 'affiliation'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, fn ($q) => $q->where('role', $this->roleFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'userRoles' => UserRole::cases(),
        ])->layout('layouts.app', ['title' => 'จัดการผู้ใช้']);
    }
}
