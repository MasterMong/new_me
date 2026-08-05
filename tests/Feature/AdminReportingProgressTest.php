<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Reporting\CourseProgress;
use App\Livewire\Admin\Reporting\UserProgress;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('course details report shows real progress percentage, not a mock', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $doneContent = ModuleContent::factory()->create(['module_id' => $module->id]);
    $doneContent->views()->create(['user_id' => $learner->id, 'is_completed' => true, 'viewed_at' => now()]);

    ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(CourseProgress::class)
        ->call('selectCourse', $course->id)
        ->assertViewHas('enrollments', function ($enrollments) {
            return $enrollments->first()->progress_percent === 50;
        });
});

test('course details progress ignores content restricted to another group', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $visible = ModuleContent::factory()->create(['module_id' => $module->id]);
    $visible->views()->create(['user_id' => $learner->id, 'is_completed' => true, 'viewed_at' => now()]);

    $restricted = ModuleContent::factory()->create(['module_id' => $module->id]);
    $restrictedGroup = LearnerGroup::factory()->create();
    $restricted->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(CourseProgress::class)
        ->call('selectCourse', $course->id)
        ->assertViewHas('enrollments', function ($enrollments) {
            return $enrollments->first()->progress_percent === 100;
        });
});

test('user details report shows real progress percentage, not a mock', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    ModuleContent::factory()->create(['module_id' => $module->id]);
    ModuleContent::factory()->create(['module_id' => $module->id]);
    ModuleContent::factory()->create(['module_id' => $module->id]);
    ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(UserProgress::class)
        ->call('selectUser', $learner->id)
        ->assertViewHas('user', function ($user) {
            return $user->enrollments->first()->progress_percent === 0;
        });
});

test('course details enrollment list can be filtered by enrollment date range', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $inRange = User::factory()->create(['role' => UserRole::Learner->value]);
    $outOfRange = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    Enrollment::factory()->create([
        'user_id' => $inRange->id,
        'course_id' => $course->id,
        'enrolled_at' => '2026-03-15',
    ]);
    Enrollment::factory()->create([
        'user_id' => $outOfRange->id,
        'course_id' => $course->id,
        'enrolled_at' => '2026-01-01',
    ]);

    $this->actingAs($admin);

    Livewire::test(CourseProgress::class)
        ->call('selectCourse', $course->id)
        ->set('enrolledFrom', '2026-03-01')
        ->set('enrolledTo', '2026-03-31')
        ->assertViewHas('enrollments', function ($enrollments) use ($inRange) {
            return $enrollments->total() === 1 && $enrollments->first()->user_id === $inRange->id;
        });
});

test('user progress list can be filtered by enrollment date range', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $inRange = User::factory()->create(['role' => UserRole::Learner->value, 'first_name' => 'InRange']);
    $outOfRange = User::factory()->create(['role' => UserRole::Learner->value, 'first_name' => 'OutOfRange']);
    $course = Course::factory()->create();

    Enrollment::factory()->create([
        'user_id' => $inRange->id,
        'course_id' => $course->id,
        'enrolled_at' => '2026-03-15',
    ]);
    Enrollment::factory()->create([
        'user_id' => $outOfRange->id,
        'course_id' => $course->id,
        'enrolled_at' => '2026-01-01',
    ]);

    $this->actingAs($admin);

    Livewire::test(UserProgress::class)
        ->set('enrolledFrom', '2026-03-01')
        ->set('enrolledTo', '2026-03-31')
        ->assertSee('InRange')
        ->assertDontSee('OutOfRange');
});

test('user details module breakdown reflects completion, locking, and post-test status', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    $content1 = ModuleContent::factory()->create(['module_id' => $module1->id]);
    $content1->views()->create(['user_id' => $learner->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
    ]);

    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    ModuleContent::factory()->create(['module_id' => $module2->id]);

    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(UserProgress::class)
        ->call('selectUser', $learner->id)
        ->assertViewHas('user', function ($user) {
            $breakdown = $user->enrollments->first()->module_breakdown;

            return $breakdown[0]['status'] === 'completed'
                && $breakdown[0]['post_test_passed'] === true
                && $breakdown[1]['status'] === 'not_started';
        });
});
