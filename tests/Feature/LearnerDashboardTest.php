<?php

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Learner\Dashboard;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
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

test('an in-progress course is featured as the active enrollment and a completed one is badged', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);

    $inProgressCourse = Course::factory()->create(['title' => 'In Progress Course']);
    $inProgressModule = Module::factory()->create(['course_id' => $inProgressCourse->id]);
    $watchedContent = ModuleContent::factory()->create([
        'module_id' => $inProgressModule->id,
        'content_type' => ContentType::Document->value,
    ]);
    ModuleContent::factory()->create([
        'module_id' => $inProgressModule->id,
        'content_type' => ContentType::Document->value,
    ]);
    $watchedContent->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $inProgressCourse->id]);

    $completedCourse = Course::factory()->create(['title' => 'Completed Course']);
    $completedModule = Module::factory()->create(['course_id' => $completedCourse->id]);
    $completedContent = ModuleContent::factory()->create([
        'module_id' => $completedModule->id,
        'content_type' => ContentType::Document->value,
    ]);
    $completedContent->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $completedCourse->id]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertViewHas('activeEnrollment', fn ($enrollment) => $enrollment->course->is($inProgressCourse))
        ->assertViewHas('otherEnrollments', fn ($others) => $others->contains(fn ($e) => $e->course->is($completedCourse)))
        ->assertSee('กำลังเรียนอยู่')
        ->assertSee('เรียนจบแล้ว');
});
