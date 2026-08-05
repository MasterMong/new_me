<?php

use App\Enums\UserRole;
use App\Livewire\Learner\CoursePlayer;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use App\Models\UserGroupMembership;
use Livewire\Livewire;

test('learner can access course player if enrolled', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $this->get(route('learn.courses.play', [$course, $module]))
        ->assertOk();
});

test('course player shows module contents', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'Video Lesson 1',
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('Video Lesson 1');
});

test('sequential content locks next items', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create([
        'course_id' => $course->id,
        'is_sequential' => true,
    ]);

    $content1 = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);
    $content2 = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertViewHas('contents', function ($contents) {
            return $contents->firstWhere('sort_order', 1)->is_accessible === true &&
                   $contents->firstWhere('sort_order', 2)->is_accessible === false;
        });
});

test('learner can update progress', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('updateProgress', 60, 30, true);

    $this->assertDatabaseHas('content_views', [
        'user_id' => $user->id,
        'content_id' => $content->id,
        'is_completed' => true,
        'watch_duration_sec' => 60,
    ]);
});

test('group-restricted content is hidden from learners outside the group', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $openContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'Open Lesson',
        'sort_order' => 1,
    ]);

    $restrictedContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'STOP Only Lesson',
        'sort_order' => 2,
    ]);

    $restrictedGroup = LearnerGroup::factory()->create(['name' => 'สตผ.']);
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('Open Lesson')
        ->assertDontSee('STOP Only Lesson');
});

test('group-restricted content is visible to members of that group', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $restrictedContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'STOP Only Lesson',
    ]);

    $restrictedGroup = LearnerGroup::factory()->create(['name' => 'สตผ.']);
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    UserGroupMembership::create([
        'user_id' => $user->id,
        'group_id' => $restrictedGroup->id,
        'assigned_at' => now(),
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('STOP Only Lesson');
});

test('learner cannot deep-link directly to group-restricted content', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $restrictedContent = ModuleContent::factory()->create(['module_id' => $module->id]);
    $restrictedGroup = LearnerGroup::factory()->create();
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, [
        'course' => $course,
        'module' => $module,
        'content' => $restrictedContent,
    ])->assertStatus(403);
});
