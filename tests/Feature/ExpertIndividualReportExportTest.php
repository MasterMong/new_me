<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Expert\IndividualReport;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('expert can export a learner\'s report as csv', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);

    $this->actingAs($expert);

    Livewire::test(IndividualReport::class, ['user' => $learner])
        ->call('exportCsv')
        ->assertFileDownloaded('ผลการเรียน-'.str_replace(' ', '_', $learner->fullName()).'-'.now()->format('Y-m-d').'.csv');
});

test('the exported csv contains the learner\'s pre-test, worksheet and post-test attempts', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    $preTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PreTest->value,
        'title' => 'แบบทดสอบก่อนเรียน โมดูล 1',
    ]);
    $assignment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::Assignment->value,
        'title' => 'ใบงานโมดูล 1',
    ]);

    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $preTest->id,
        'total_score' => 8,
        'max_score' => 10,
        'status' => TestAttemptStatus::Submitted->value,
        'submitted_at' => now(),
    ]);
    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $assignment->id,
        'status' => TestAttemptStatus::PendingReview->value,
        'submitted_at' => now(),
    ]);

    // Another learner's attempt must not leak into this export.
    $otherLearner = User::factory()->create(['role' => UserRole::Learner->value]);
    TestAttempt::factory()->create([
        'user_id' => $otherLearner->id,
        'assessment_id' => $preTest->id,
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert);

    $response = Livewire::test(IndividualReport::class, ['user' => $learner])->call('exportCsv');

    $csv = base64_decode(data_get($response->effects, 'download.content'));

    expect($csv)
        ->toContain($module->title)
        ->toContain('8')
        ->toContain('10')
        ->toContain('รอตรวจ');
});
