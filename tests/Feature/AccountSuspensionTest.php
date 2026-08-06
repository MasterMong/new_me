<?php

use App\Enums\UserRole;
use App\Models\User;

test('a suspended admin is logged out and redirected when hitting an admin route', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => false]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a suspended learner is logged out and redirected when hitting a learner route', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner, 'is_active' => false]);

    $response = $this->actingAs($learner)->get(route('learn.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a suspended expert is logged out and redirected when hitting an expert route', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert, 'is_active' => false]);

    $response = $this->actingAs($expert)->get(route('expert.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('an active user is not affected by the suspension check', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
});
