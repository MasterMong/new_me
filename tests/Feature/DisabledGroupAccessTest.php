<?php

use App\Enums\UserRole;
use App\Models\ContentGroupAccess;
use App\Models\Course;
use App\Models\CourseGroupAccess;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use App\Models\UserGroupMembership;

test('disabling a group revokes course access it was granting', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create(['is_active' => true]);
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);
    UserGroupMembership::create(['user_id' => $learner->id, 'group_id' => $group->id, 'assigned_at' => now()]);

    expect($course->fresh()->isVisibleTo($learner))->toBeTrue();

    $group->update(['is_active' => false]);

    expect($course->fresh()->isVisibleTo($learner))->toBeFalse();
});

test('disabling a group revokes content access it was granting', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);
    $group = LearnerGroup::factory()->create(['is_active' => true]);
    ContentGroupAccess::create(['content_id' => $content->id, 'group_id' => $group->id]);
    UserGroupMembership::create(['user_id' => $learner->id, 'group_id' => $group->id, 'assigned_at' => now()]);

    expect($content->fresh()->isVisibleTo($learner))->toBeTrue();

    $group->update(['is_active' => false]);

    expect($content->fresh()->isVisibleTo($learner))->toBeFalse();
});

test('scopeVisibleTo also excludes memberships in a disabled group', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $visibleCourse = Course::factory()->create(['is_published' => true]);
    $restrictedCourse = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create(['is_active' => false]);
    CourseGroupAccess::create(['course_id' => $restrictedCourse->id, 'group_id' => $group->id]);
    UserGroupMembership::create(['user_id' => $learner->id, 'group_id' => $group->id, 'assigned_at' => now()]);

    $visibleIds = Course::visibleTo($learner)->pluck('id');

    expect($visibleIds)->toContain($visibleCourse->id)
        ->not->toContain($restrictedCourse->id);
});
