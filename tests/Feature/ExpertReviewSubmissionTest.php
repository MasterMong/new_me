<?php

use App\Enums\UserRole;
use App\Livewire\Expert\ReviewSubmission;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\ExpertReview;
use App\Models\Module;
use App\Models\Question;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use App\Notifications\ExpertReviewCompleted;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('review page renders the learner essay text and file download link', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $essayQuestion = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => 'essay',
        'question_text' => 'อธิบายกระบวนการติดตามและประเมินผล',
    ]);
    $fileQuestion = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => 'file_upload',
        'question_text' => 'แนบไฟล์แผนการติดตาม',
    ]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    TestAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $essayQuestion->id,
        'essay_text' => 'คำตอบทดสอบของผู้เรียนสำหรับข้อเขียนบรรยาย',
    ]);
    TestAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $fileQuestion->id,
        'uploaded_file_url' => 'http://localhost/storage/worksheets/1/plan.pdf',
    ]);

    $response = $this->actingAs($expert)->get(route('expert.submissions.review', $attempt->id));

    $response->assertOk();
    $response->assertSee('อธิบายกระบวนการติดตามและประเมินผล');
    $response->assertSee('คำตอบทดสอบของผู้เรียนสำหรับข้อเขียนบรรยาย');
    $response->assertSee('แนบไฟล์แผนการติดตาม');
    $response->assertSee('http://localhost/storage/worksheets/1/plan.pdf', false);
});

test('review page renders for a course-level assessment with no module', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'requires_expert_review' => true,
    ]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert)
        ->get(route('expert.submissions.review', $attempt->id))
        ->assertOk()
        ->assertSee($assessment->title);
});

test('review page does not crash when an existing expert review has no feedback yet', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    ExpertReview::create([
        'attempt_id' => $attempt->id,
        'expert_id' => $expert->id,
        'status' => 'pending',
        'score' => null,
        'feedback' => null,
    ]);

    $this->actingAs($expert)
        ->get(route('expert.submissions.review', $attempt->id))
        ->assertOk();
});

test('expert marking a submission passed creates a review and updates the attempt', function () {
    Notification::fake();

    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'max_score' => 10,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert);

    Livewire::test(ReviewSubmission::class, ['attempt' => $attempt])
        ->set('status', 'passed')
        ->set('score', 8)
        ->set('feedback', 'ทำได้ดีมาก ครบถ้วนตามเกณฑ์')
        ->call('submitReview')
        ->assertRedirect(route('expert.submissions.index', $module->id));

    $this->assertDatabaseHas('expert_reviews', [
        'attempt_id' => $attempt->id,
        'expert_id' => $expert->id,
        'status' => 'passed',
        'score' => 8,
        'feedback' => 'ทำได้ดีมาก ครบถ้วนตามเกณฑ์',
    ]);

    $attempt->refresh();
    expect($attempt->status->value)->toBe('passed')
        ->and($attempt->reviewed_by)->toBe($expert->id)
        ->and($attempt->reviewed_at)->not->toBeNull()
        ->and((float) $attempt->total_score)->toBe(8.0)
        ->and((float) $attempt->score_pct)->toBe(80.0);

    Notification::assertSentTo($learner, ExpertReviewCompleted::class);
});

test('expert marking a submission revision_needed does not issue a certificate', function () {
    Notification::fake();

    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'max_score' => 10,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert);

    Livewire::test(ReviewSubmission::class, ['attempt' => $attempt])
        ->set('status', 'revision_needed')
        ->set('feedback', 'กรุณาแก้ไขคำตอบข้อ 1 เพิ่มเติม')
        ->call('submitReview');

    $attempt->refresh();
    expect($attempt->status->value)->toBe('revision_needed');

    $this->assertDatabaseMissing('certificates', ['user_id' => $learner->id, 'course_id' => $course->id]);
    Notification::assertSentTo($learner, ExpertReviewCompleted::class);
});

test('score above the attempt max score is rejected', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'max_score' => 10,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert);

    Livewire::test(ReviewSubmission::class, ['attempt' => $attempt])
        ->set('status', 'passed')
        ->set('score', 15)
        ->set('feedback', 'คะแนนเกินเกณฑ์ที่กำหนดไว้')
        ->call('submitReview')
        ->assertHasErrors(['score' => 'max']);

    $this->assertDatabaseMissing('expert_reviews', ['attempt_id' => $attempt->id]);

    $attempt->refresh();
    expect($attempt->status->value)->toBe('pending_review');
});
