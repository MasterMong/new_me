<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\CoursePath;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
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
