<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('a pre-test attempt is marked Submitted, not Passed, when the score clears the passing threshold', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PreTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $correctChoice->id)
        ->call('finish')
        ->assertSet('score', 100)
        ->assertViewHas('currentAttempt', fn ($attempt) => $attempt->status === TestAttemptStatus::Submitted);

    $this->assertDatabaseHas('test_attempts', [
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Submitted->value,
        'score_pct' => 100,
    ]);
});

test('a pre-test attempt is marked Submitted, not Failed, when the score misses the passing threshold', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PreTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish')
        ->assertSet('score', 0)
        ->assertViewHas('currentAttempt', fn ($attempt) => $attempt->status === TestAttemptStatus::Submitted);

    $this->assertDatabaseHas('test_attempts', [
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Submitted->value,
        'score_pct' => 0,
    ]);
});

test('a pre-test attempt never gets a star_rating', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PreTest->value, 'max_attempts' => 3]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment]);

    $this->assertDatabaseHas('test_attempts', [
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'star_rating' => null,
    ]);
});

test('a 0% pre-test result shows a neutral completion screen, not a pass/fail verdict', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PreTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish');

    $component->assertSee('ทำแบบทดสอบก่อนเรียนเสร็จสิ้น')
        ->assertSee('0%')
        ->assertDontSee('ผ่านเกณฑ์')
        ->assertDontSee('ไม่ผ่านเกณฑ์')
        ->assertDontSee('พยายามใหม่อีกครั้ง')
        ->assertDontSee('ทำแบบทดสอบใหม่')
        ->assertDontSee('text-secondary', false);
});

test('canRetry is false for a submitted pre-test attempt', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PreTest->value, 'max_attempts' => 3]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish')
        ->call('retryAttempt');

    // retryAttempt() is a no-op since canRetry() is false — still only one attempt.
    expect(TestAttempt::where('assessment_id', $assessment->id)->count())->toBe(1);
});

test('a passed post-test still shows the normal pass/fail results screen (regression)', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $correctChoice->id)
        ->call('finish')
        ->assertSee('ยินดีด้วย! คุณสอบผ่าน')
        ->assertSee('ผ่านเกณฑ์')
        ->assertDontSee('ทำแบบทดสอบก่อนเรียนเสร็จสิ้น');
});

test('the learner result PDF does not cross-contaminate a module pre-test with its post-test', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $modulePreTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PreTest->value,
        'passing_score_pct' => 90,
    ]);
    $modulePostTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PostTest->value,
        'passing_score_pct' => 50,
    ]);

    // Pre-test scored low (it's diagnostic, this is fine); post-test passed.
    TestAttempt::create([
        'user_id' => $user->id, 'assessment_id' => $modulePreTest->id, 'attempt_number' => 1,
        'status' => TestAttemptStatus::Submitted->value, 'score_pct' => 10,
        'started_at' => now(), 'submitted_at' => now(),
    ]);
    TestAttempt::create([
        'user_id' => $user->id, 'assessment_id' => $modulePostTest->id, 'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value, 'score_pct' => 80,
        'started_at' => now(), 'submitted_at' => now(),
    ]);

    $enrollment = Enrollment::with([
        'course.modules.assessments.attempts',
    ])->find(Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()])->id);

    // Render the report view directly (bypassing PDF binary compilation, which
    // isn't reliably assertable as text) — this is the exact view+data pair
    // Results::downloadPdf() feeds to the PDF renderer.
    $html = (string) $this->view('pdf.learner-result', ['enrollment' => $enrollment, 'user' => $user]);

    // The module row must reflect the post-test's 80%/passed outcome, not the
    // pre-test's 10% — an unfiltered assessments->first() would have picked
    // whichever was created first, which could be the pre-test.
    expect($html)->toContain('80%')
        ->not->toContain('10%');
});
