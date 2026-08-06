<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('admin can mark an assessment as required for certificate issuance', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบบังคับผ่าน')
        ->set('assessmentType', AssessmentType::ModuleTest->value)
        ->set('assessmentIsRequiredForCert', true)
        ->call('saveAssessment');

    $this->assertDatabaseHas('assessments', [
        'title' => 'แบบทดสอบบังคับผ่าน',
        'is_required_for_cert' => true,
    ]);
});

test('editing an assessment preserves and can toggle is_required_for_cert', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'is_required_for_cert' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('editAssessment', $assessment->id)
        ->assertSet('assessmentIsRequiredForCert', false)
        ->set('assessmentIsRequiredForCert', true)
        ->call('saveAssessment');

    expect($assessment->fresh()->is_required_for_cert)->toBeTrue();
});

test('a required-for-cert assessment actually blocks certificate issuance until passed', function () {
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $enrollment = Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'type' => AssessmentType::Assignment,
        'is_required_for_cert' => true,
    ]);

    // No passed attempt yet -> not eligible even though there's nothing else required.
    expect($enrollment->fresh()->isEligibleForCertificate())->toBeFalse();

    TestAttempt::factory()->create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Passed,
    ]);

    CourseReview::create([
        'user_id' => $learner->id,
        'course_id' => $course->id,
        'rating' => 5,
    ]);

    expect($enrollment->fresh()->isEligibleForCertificate())->toBeTrue();
});
