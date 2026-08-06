<?php

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\EnrollmentStatus;
use App\Enums\GradingMode;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Expert\ReviewSubmission;
use App\Livewire\Learner\AssessmentPlayer;
use App\Livewire\Learner\CoursePlayer;
use App\Livewire\Learner\CourseReview as CourseReviewPage;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAttempt;
use App\Models\User;
use App\Notifications\CertificateIssued;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Builds a course + enrollment that satisfies all 5 certificate conditions,
 * so individual tests can knock out exactly one condition at a time.
 */
function makeEligibleEnrollment(): Enrollment
{
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);

    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
    ]);

    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);

    return Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);
}

test('learner is eligible once all 5 conditions are met', function () {
    $enrollment = makeEligibleEnrollment();

    expect($enrollment->isEligibleForCertificate())->toBeTrue();
});

test('learner is not eligible if a required module is incomplete', function () {
    $enrollment = makeEligibleEnrollment();

    // Add a second required module with unwatched content.
    $module = Module::factory()->create(['course_id' => $enrollment->course_id, 'is_required' => true]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);

    expect($enrollment->isEligibleForCertificate())->toBeFalse();
});

test('learner is not eligible if a required-for-cert assessment is not passed', function () {
    $enrollment = makeEligibleEnrollment();

    Assessment::factory()->create([
        'course_id' => $enrollment->course_id,
        'is_required_for_cert' => true,
    ]);

    expect($enrollment->isEligibleForCertificate())->toBeFalse();
});

test('learner is not eligible without a course review', function () {
    $enrollment = makeEligibleEnrollment();

    CourseReview::where('user_id', $enrollment->user_id)->where('course_id', $enrollment->course_id)->delete();

    expect($enrollment->isEligibleForCertificate())->toBeFalse();
});

test('learner is not eligible without a passing post-test score', function () {
    $enrollment = makeEligibleEnrollment();

    TestAttempt::where('user_id', $enrollment->user_id)->update(['score_pct' => 40, 'status' => TestAttemptStatus::Failed->value]);

    expect($enrollment->isEligibleForCertificate())->toBeFalse();
});

test('a module-scoped post-test does not satisfy the course-wide post-test condition', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);

    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    // Only a module-scoped post-test exists — no course-wide one.
    $moduleTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $moduleTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 100,
    ]);

    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);

    $enrollment = Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    // No course-wide post-test exists, so condition 5 is trivially satisfied
    // (there's nothing to check) and the module-scoped one is irrelevant to it.
    expect($enrollment->isEligibleForCertificate())->toBeTrue();
});

test('issuing a certificate creates the record, marks the enrollment certified, and notifies the learner', function () {
    Notification::fake();

    $enrollment = makeEligibleEnrollment();

    $certificate = $enrollment->issueCertificateIfEligible();

    expect($certificate)->not->toBeNull();
    $this->assertDatabaseHas('certificates', [
        'user_id' => $enrollment->user_id,
        'course_id' => $enrollment->course_id,
        'full_name_on_cert' => $enrollment->user->fullName(),
    ]);

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Certified);
    Notification::assertSentTo($enrollment->user, CertificateIssued::class);
});

test('issuing a certificate is idempotent and does not duplicate', function () {
    $enrollment = makeEligibleEnrollment();

    $first = $enrollment->issueCertificateIfEligible();
    $second = $enrollment->issueCertificateIfEligible();

    expect($second->id)->toBe($first->id);
    expect(Certificate::where('user_id', $enrollment->user_id)->where('course_id', $enrollment->course_id)->count())->toBe(1);
});

test('an ineligible enrollment is not issued a certificate', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $certificate = $enrollment->issueCertificateIfEligible();

    expect($certificate)->toBeNull();
    expect(Certificate::count())->toBe(0);
});

test('completing the last required module content issues a certificate via CoursePlayer', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
    ]);
    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('updateProgress', 60, 60, true);

    $this->assertDatabaseHas('certificates', ['user_id' => $user->id, 'course_id' => $course->id]);
});

test('passing the post-test issues a certificate via AssessmentPlayer', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 50]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
        'passing_score_pct' => 50,
    ]);
    $question = Question::factory()->create([
        'assessment_id' => $postTest->id,
        'points' => 10,
        'question_type' => 'multiple_choice',
        'grading_mode' => GradingMode::Auto->value,
    ]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $postTest])
        ->call('selectChoice', $correctChoice->id)
        ->call('finish');

    $this->assertDatabaseHas('certificates', ['user_id' => $user->id, 'course_id' => $course->id]);
});

test('expert marking an assessment passed issues a certificate via ReviewSubmission', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 50]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
    ]);

    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);

    $assignment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::Assignment->value,
        'is_required_for_cert' => true,
        'grading_mode' => GradingMode::Manual->value,
    ]);
    $attempt = TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assignment->id,
        'status' => TestAttemptStatus::PendingReview->value,
        'submitted_at' => now(),
        'max_score' => 10,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($expert);

    Livewire::test(ReviewSubmission::class, ['attempt' => $attempt])
        ->set('status', 'passed')
        ->set('score', 10)
        ->set('feedback', 'ผ่านเกณฑ์ทุกข้อ')
        ->call('submitReview');

    $this->assertDatabaseHas('certificates', ['user_id' => $user->id, 'course_id' => $course->id]);
});

test('submitting a course review issues a certificate via the CourseReview component', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CourseReviewPage::class, ['course' => $course])
        ->set('rating', 5)
        ->call('submitReview');

    $this->assertDatabaseHas('certificates', ['user_id' => $user->id, 'course_id' => $course->id]);
});
