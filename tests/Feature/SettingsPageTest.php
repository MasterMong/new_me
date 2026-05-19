<?php

use App\Enums\UserExperience;
use App\Enums\UserPrefix;
use App\Enums\UserRole;
use App\Livewire\Learner\Settings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('learner can update their profile', function () {
    $user = User::factory()->create([
        'role' => UserRole::Learner->value,
        'first_name' => 'OldName',
    ]);

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->set('first_name', 'NewName')
        ->set('prefix', UserPrefix::Nai->value)
        ->set('experience', UserExperience::TwoToFive->value)
        ->call('updateProfile');

    $this->assertEquals('NewName', $user->fresh()->first_name);
});

test('learner can change their password', function () {
    $user = User::factory()->create([
        'role' => UserRole::Learner->value,
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->set('current_password', 'old-password')
        ->set('new_password', 'new-password123')
        ->set('new_password_confirmation', 'new-password123')
        ->call('updatePassword');

    $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
});

test('learner can toggle email notifications', function () {
    $user = User::factory()->create([
        'role' => UserRole::Learner->value,
        'email_notifications_enabled' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->set('email_notifications_enabled', false)
        ->call('updateNotifications');

    $this->assertFalse($user->fresh()->email_notifications_enabled);
});
