<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\CoursePath;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('learner can view course path if enrolled', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $this->get(route('learn.courses.show', $course))
        ->assertOk()
        ->assertSee($course->title);
});

test('learner is redirected if not enrolled', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $this->actingAs($user);

    $this->get(route('learn.courses.show', $course))
        ->assertRedirect(route('courses.show', $course));
});

test('modules are locked if pre-test is not completed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $preTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'type' => AssessmentType::PreTest->value,
    ]);

    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->first()->is_accessible === false;
        });
});

test('module is accessible after pre-test is completed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $preTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'type' => AssessmentType::PreTest->value,
    ]);

    // Simulate pre-test completion
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $preTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 100,
    ]);

    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->first()->is_accessible === true;
        });
});

test('module progress ignores content restricted to another group', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $module = Module::factory()->create(['course_id' => $course->id]);

    $visibleContent = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);
    $visibleContent->views()->create([
        'user_id' => $user->id,
        'is_completed' => true,
        'viewed_at' => now(),
    ]);

    $restrictedContent = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 2]);
    $restrictedGroup = LearnerGroup::factory()->create();
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            // Denominator should be the 1 visible content item, not the 2 total (which would give 50%).
            return $modules->first()->progress_percent === 100;
        });
});

test('next module is locked until the previous module assignment is passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);

    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::Assignment->value,
    ]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->firstWhere('sort_order', 2)->is_accessible === false;
        });
});

test('next module unlocks once the previous module assignment is passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);

    $assignment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::Assignment->value,
    ]);

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assignment->id,
        'status' => TestAttemptStatus::Passed->value,
    ]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->firstWhere('sort_order', 2)->is_accessible === true;
        });
});

test('next module unlocks even without a passed attempt when the previous module has no assignment', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->firstWhere('sort_order', 2)->is_accessible === true;
        });
});

test('module is still locked by an unpassed assignment even after a revision_needed review', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);

    $assignment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::Assignment->value,
    ]);

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assignment->id,
        'status' => TestAttemptStatus::RevisionNeeded->value,
    ]);

    $this->actingAs($user);

    Livewire::test(CoursePath::class, ['course' => $course])
        ->assertViewHas('modules', function ($modules) {
            return $modules->firstWhere('sort_order', 2)->is_accessible === false;
        });
});
