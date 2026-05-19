<?php

use App\Enums\UserRole;
use App\Models\User;

test('admin can access courses management', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.courses.index'));

    $response->assertOk();
    $response->assertSee('จัดการคอร์ส');
});

test('admin can access reports management', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.reports.index'));

    $response->assertOk();
    $response->assertSee('รายงานภาพรวม');
});
