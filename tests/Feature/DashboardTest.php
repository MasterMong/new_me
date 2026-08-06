<?php

use App\Enums\UserRole;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('a learner visiting /dashboard is redirected to their learner dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('learn.dashboard'));
});

test('an expert visiting /dashboard is redirected to their expert dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Expert->value]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('expert.dashboard'));
});

test('an admin visiting /dashboard is redirected to their admin dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('admin.dashboard'));
});
