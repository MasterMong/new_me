<?php

namespace App\Livewire\Learner;

use App\Enums\UserExperience;
use App\Enums\UserPrefix;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ตั้งค่าบัญชี')]
class Settings extends Component
{
    // Profile
    public $prefix;

    public $first_name;

    public $last_name;

    public $phone;

    public $school_name;

    public $experience;

    // Security
    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    // Notifications
    public $email_notifications_enabled;

    public function mount()
    {
        $user = Auth::user();
        $this->prefix = $user->prefix->value ?? null;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->phone = $user->phone;
        $this->school_name = $user->school_name;
        $this->experience = $user->experience->value ?? null;
        $this->email_notifications_enabled = $user->email_notifications_enabled;
    }

    public function updateProfile()
    {
        $this->validate([
            'prefix' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'experience' => ['required', 'string'],
        ]);

        Auth::user()->update([
            'prefix' => $this->prefix,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'school_name' => $this->school_name,
            'experience' => $this->experience,
        ]);

        $this->dispatch('toast', message: 'อัปเดตโปรไฟล์เรียบร้อยแล้ว', type: 'success');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('toast', message: 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว', type: 'success');
    }

    public function updateNotifications()
    {
        Auth::user()->update([
            'email_notifications_enabled' => $this->email_notifications_enabled,
        ]);

        $this->dispatch('toast', message: 'บันทึกการตั้งค่าแจ้งเตือนแล้ว', type: 'success');
    }

    public function render()
    {
        return view('livewire.learner.settings', [
            'prefixes' => UserPrefix::cases(),
            'experiences' => UserExperience::cases(),
        ]);
    }
}
