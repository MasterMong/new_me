<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Reporting\UserProgress;
use App\Models\User;
use Livewire\Livewire;

test('visiting user-progress with a selectedUserId query string opens that user detail view directly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);

    $this->actingAs($admin);

    Livewire::withQueryParams(['selectedUserId' => $learner->id])
        ->test(UserProgress::class)
        ->assertSet('selectedUserId', $learner->id)
        ->assertSee($learner->fullName());
});
