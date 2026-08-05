<?php

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Reporting\ExpertTurnaround;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\ExpertReview;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('admin can view the expert turnaround report', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $response = $this->actingAs($admin)->get(route('admin.reporting.expert-turnaround'));

    $response->assertOk();
    $response->assertSee('ความเร็วในการตรวจของผู้เชี่ยวชาญ');
});

test('completed review count and average turnaround are computed per expert', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $attempt = TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Passed->value,
        'submitted_at' => now()->subHours(4),
    ]);

    ExpertReview::create([
        'attempt_id' => $attempt->id,
        'expert_id' => $expert->id,
        'status' => 'passed',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(ExpertTurnaround::class)
        ->assertViewHas('experts', function ($experts) use ($expert) {
            $row = $experts->firstWhere('expert.id', $expert->id);

            return $row['completed_count'] === 1 && $row['avg_turnaround_hours'] === 4.0;
        });
});

test('pending count only reflects modules assigned to that expert', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $assignedExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $otherExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $assignedExpert->id,
        'assigned_at' => now(),
    ]);

    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::PendingReview->value,
    ]);

    $this->actingAs($admin);

    Livewire::test(ExpertTurnaround::class)
        ->assertViewHas('experts', function ($experts) use ($assignedExpert, $otherExpert) {
            $assignedRow = $experts->firstWhere('expert.id', $assignedExpert->id);
            $otherRow = $experts->firstWhere('expert.id', $otherExpert->id);

            return $assignedRow['pending_count'] === 1 && $otherRow['pending_count'] === 0;
        });
});

test('pending reviews for modules with no expert assignment count toward the open queue', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::Assignment->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::PendingReview->value,
    ]);

    $this->actingAs($admin);

    Livewire::test(ExpertTurnaround::class)
        ->assertViewHas('openQueueCount', 1);
});
