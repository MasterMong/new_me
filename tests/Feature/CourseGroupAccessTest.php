<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Create as AdminCoursesCreate;
use App\Livewire\Admin\Courses\Edit as AdminCoursesEdit;
use App\Livewire\Public\Courses\Index as PublicCoursesIndex;
use App\Livewire\Public\Courses\Show as PublicCoursesShow;
use App\Models\Course;
use App\Models\CourseGroupAccess;
use App\Models\LearnerGroup;
use App\Models\User;
use App\Models\UserGroupMembership;
use Livewire\Livewire;

test('a course with no group restriction is visible to guests', function () {
    $course = Course::factory()->create(['is_published' => true]);

    $this->get(route('courses.index'))->assertSee($course->title);
    $this->get(route('courses.show', $course))->assertOk();
});

test('a group-restricted course is hidden from guests', function () {
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    $this->get(route('courses.index'))->assertDontSee($course->title);
    $this->get(route('courses.show', $course))->assertForbidden();
});

test('a group-restricted course is hidden from a learner who is not a member', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    $this->actingAs($learner);

    Livewire::test(PublicCoursesIndex::class)->assertDontSee($course->title);

    $this->get(route('courses.show', $course))->assertForbidden();
});

test('a group-restricted course is visible to a member learner', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    UserGroupMembership::create([
        'user_id' => $learner->id,
        'group_id' => $group->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($learner);

    Livewire::test(PublicCoursesIndex::class)->assertSee($course->title);

    $this->get(route('courses.show', $course))->assertOk();
});

test('a learner whose group membership is revoked after loading the page cannot enroll via the defensive check', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    $membership = UserGroupMembership::create([
        'user_id' => $learner->id,
        'group_id' => $group->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($learner);

    // Page loads fine while still a member (mount() allows it).
    $component = Livewire::test(PublicCoursesShow::class, ['course' => $course])->assertOk();

    // Membership revoked after the page already loaded.
    $membership->delete();

    // enroll()'s own defensive check catches it independently of mount().
    $component->call('enroll')->assertForbidden();

    $this->assertDatabaseMissing('enrollments', [
        'user_id' => $learner->id,
        'course_id' => $course->id,
    ]);
});

test('a member learner can enroll in a restricted course', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['is_published' => true]);
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    UserGroupMembership::create([
        'user_id' => $learner->id,
        'group_id' => $group->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($learner);

    Livewire::test(PublicCoursesShow::class, ['course' => $course])
        ->call('enroll');

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $learner->id,
        'course_id' => $course->id,
    ]);
});

test('admin can restrict a new course to specific groups', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $group = LearnerGroup::factory()->create();

    $this->actingAs($admin);

    Livewire::test(AdminCoursesCreate::class)
        ->set('title', 'หลักสูตรจำกัดกลุ่ม')
        ->set('passingScorePct', 60)
        ->set('selectedGroupIds', [(string) $group->id])
        ->call('save');

    $course = Course::where('title', 'หลักสูตรจำกัดกลุ่ม')->firstOrFail();

    $this->assertDatabaseHas('course_group_access', [
        'course_id' => $course->id,
        'group_id' => $group->id,
    ]);
});

test('admin can update and clear a course group restriction', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $group = LearnerGroup::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesEdit::class, ['course' => $course])
        ->assertSet('selectedGroupIds', [(string) $group->id])
        ->set('selectedGroupIds', [])
        ->call('save');

    $this->assertDatabaseMissing('course_group_access', [
        'course_id' => $course->id,
        'group_id' => $group->id,
    ]);
});
