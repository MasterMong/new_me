<?php

use App\Enums\UserRole;
use App\Models\User;

test('admin can access dashboard', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('แดชบอร์ดผู้ดูแลระบบ');
});

test('admin can access users list', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertSee('จัดการผู้ใช้');
});

test('admin can access groups list', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.groups.index'));

    $response->assertOk();
    $response->assertSee('จัดการกลุ่ม');
});

test('learner cannot access admin pages', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner]);

    $response = $this->actingAs($learner)->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

test('expert cannot access admin pages', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert]);

    $response = $this->actingAs($expert)->get(route('admin.dashboard'));

    $response->assertStatus(403);
});
