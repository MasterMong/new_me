<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserExperience;
use App\Enums\UserPrefix;
use App\Enums\UserRole;
use App\Models\Affiliation;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    // Modal state
    public bool $showingEditModal = false;

    public ?User $editingUser = null;

    #[Validate('required|string')]
    public $prefix = '';

    #[Validate('required|string|max:100')]
    public $first_name = '';

    #[Validate('required|string|max:100')]
    public $last_name = '';

    #[Validate('required|email|max:255')]
    public $email = '';

    #[Validate('required')]
    public $role = '';

    #[Validate('nullable|exists:positions,id')]
    public $position_id = null;

    #[Validate('nullable|exists:affiliations,id')]
    public $affiliation_id = null;

    #[Validate('required|string')]
    public $experience = '';

    // Password reset state
    public bool $showingPasswordModal = false;

    public $new_password = '';

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
        $this->dispatch('toast', message: 'อัปเดตสถานะผู้ใช้เรียบร้อยแล้ว', type: 'success');
    }

    public function editUser(User $user)
    {
        $this->editingUser = $user;
        $this->prefix = $user->prefix->value;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->position_id = $user->position_id;
        $this->affiliation_id = $user->affiliation_id;
        $this->experience = $user->experience->value;

        $this->showingEditModal = true;
    }

    public function saveUser()
    {
        $this->validate();

        $this->editingUser->update([
            'prefix' => $this->prefix,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role,
            'position_id' => $this->position_id,
            'affiliation_id' => $this->affiliation_id,
            'experience' => $this->experience,
        ]);

        $this->showingEditModal = false;
        $this->dispatch('toast', message: 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว', type: 'success');
    }

    public function confirmPasswordReset(User $user)
    {
        $this->editingUser = $user;
        $this->new_password = '';
        $this->showingPasswordModal = true;
    }

    public function resetPassword()
    {
        $this->validate([
            'new_password' => 'required|min:8',
        ]);

        $this->editingUser->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->showingPasswordModal = false;
        $this->dispatch('toast', message: 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว', type: 'success');
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
            'prefixes' => UserPrefix::cases(),
            'positions' => Position::all(),
            'affiliations' => Affiliation::all(),
            'experiences' => UserExperience::cases(),
        ])->layout('layouts.app', ['title' => 'จัดการผู้ใช้']);
    }
}
