<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Livewire\Livewire;

test('admin cannot suspend their own account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

    $this->actingAs($admin);

    Livewire::test(Index::class)->call('toggleActive', $admin->id);

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('admin can suspend another account', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $other = User::factory()->create(['role' => UserRole::Learner, 'is_active' => true]);

    $this->actingAs($admin);

    Livewire::test(Index::class)->call('toggleActive', $other->id);

    expect($other->fresh()->is_active)->toBeFalse();
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('editUser', $admin)
        ->set('role', UserRole::Learner->value)
        ->call('saveUser')
        ->assertHasErrors('role');

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

test('admin can change another user role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $other = User::factory()->create(['role' => UserRole::Learner]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('editUser', $other)
        ->set('role', UserRole::Expert->value)
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($other->fresh()->role)->toBe(UserRole::Expert);
});

test('editing a user email to one already taken shows a validation error instead of crashing', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $target = User::factory()->create(['email' => 'target@example.com']);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('editUser', $target)
        ->set('email', 'taken@example.com')
        ->call('saveUser')
        ->assertHasErrors('email');

    expect($target->fresh()->email)->toBe('target@example.com');
});

test('editing a user keeping their own email does not trigger a false uniqueness error', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['email' => 'target@example.com']);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('editUser', $target)
        ->set('first_name', 'Updated')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($target->fresh()->first_name)->toBe('Updated');
});
