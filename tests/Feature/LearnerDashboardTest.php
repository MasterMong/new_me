<?php

use App\Enums\UserRole;
use App\Livewire\Learner\Dashboard;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Livewire;

test('unauthenticated users cannot access learner dashboard', function () {
    $this->get(route('learn.dashboard'))
        ->assertRedirect(route('login'));
});

test('admins can also access the learner dashboard', function () {
    $this->actingAs($user = User::factory()->admin()->create());

    // EnsureUserIsLearner intentionally allows Learner/Expert/Admin through.
    $this->get(route('learn.dashboard'))
        ->assertOk();
});

test('learners can access their dashboard', function () {
    $this->actingAs($user = User::factory()->create(['role' => UserRole::Learner->value]));

    $this->get(route('learn.dashboard'))
        ->assertOk()
        ->assertSee('ยินดีต้อนรับ');
});

test('learner dashboard shows enrolled courses', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['title' => 'Test Learning Course']);
    Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee('Test Learning Course');
});
