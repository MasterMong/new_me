<?php

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\ExpertReview;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('learner can access assessment player', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['passing_score_pct' => 80]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    $this->get(route('learn.assessments.show', $assessment))
        ->assertOk();
});

test('submit button is guarded against double submission', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['passing_score_pct' => 50]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSeeHtml('wire:target="finish"')
        ->assertSeeHtml('wire:loading.attr="disabled"');
});

test('learner can submit answers and see results', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    // Pinned to a non-pre-test type: pre-tests never get a Passed/Failed status.
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $correctChoice->id)
        ->call('finish')
        ->assertSet('score', 100)
        ->assertSet('isFinished', true)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::Passed;
        });
});

test('max attempts are enforced', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['max_attempts' => 1]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id]);

    // Create one finished attempt
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Passed,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSet('isFinished', true);
});

test('learner can save a worksheet essay answer as a draft without submitting', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create();
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, 'คำตอบร่างของฉัน')
        ->call('saveDraft')
        ->assertSet('isFinished', false);

    $this->assertDatabaseHas('test_answers', [
        'question_id' => $question->id,
        'essay_text' => 'คำตอบร่างของฉัน',
    ]);

    $this->assertDatabaseHas('test_attempts', [
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::InProgress->value,
    ]);
});

test('learner resumes a draft with previously saved answers pre-filled', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create();
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $attempt = TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::InProgress->value,
    ]);

    TestAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'essay_text' => 'ที่บันทึกไว้ก่อนหน้า',
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSet('essayAnswers.'.$question->id, 'ที่บันทึกไว้ก่อนหน้า');
});

test('submitting a worksheet with manual grading sends it to pending review', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create();
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, 'คำตอบสุดท้าย')
        ->call('finish')
        ->assertSet('isFinished', true)
        ->assertSet('score', null)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::PendingReview;
        });

    $this->assertDatabaseHas('test_answers', [
        'question_id' => $question->id,
        'essay_text' => 'คำตอบสุดท้าย',
    ]);
});

test('learner can upload a file as a worksheet answer', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create();
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::FileUpload->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('uploadedFiles.'.$question->id, UploadedFile::fake()->create('worksheet.pdf', 100))
        ->call('saveDraft');

    $answer = TestAnswer::where('question_id', $question->id)->first();

    expect($answer)->not->toBeNull();
    expect($answer->uploaded_file_url)->not->toBeNull();
});

test('learner sees expert feedback and can retry after revision is requested', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $assessment = Assessment::factory()->create(['max_attempts' => 3]);
    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $attempt = TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::RevisionNeeded->value,
    ]);

    ExpertReview::create([
        'attempt_id' => $attempt->id,
        'expert_id' => $expert->id,
        'status' => 'revision_needed',
        'feedback' => 'กรุณาอธิบายเพิ่มเติมในข้อ 1',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSet('isFinished', true)
        ->assertSee('กรุณาอธิบายเพิ่มเติมในข้อ 1')
        ->assertSet('currentAttempt.status', TestAttemptStatus::RevisionNeeded)
        ->call('retryAttempt')
        ->assertSet('isFinished', false)
        ->assertSet('currentAttempt.attempt_number', 2)
        ->assertSet('currentAttempt.status', TestAttemptStatus::InProgress);

    expect(TestAttempt::where('assessment_id', $assessment->id)->count())->toBe(2);
});

test('learner cannot retry while a worksheet is still pending expert review', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['max_attempts' => 3]);
    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::PendingReview->value,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSet('isFinished', true)
        ->call('retryAttempt')
        ->assertSet('currentAttempt.attempt_number', 1);

    expect(TestAttempt::where('assessment_id', $assessment->id)->count())->toBe(1);
});

test('checkTimeExpired does not submit before the time limit is reached', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create([
        'is_timed' => true,
        'time_limit_minutes' => 10,
    ]);
    Question::factory()->create(['assessment_id' => $assessment->id, 'question_type' => QuestionType::MultipleChoice->value]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('checkTimeExpired')
        ->assertSet('isFinished', false);
});

test('checkTimeExpired auto-submits once the time limit has elapsed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    // Pinned to a non-pre-test type: pre-tests never get a Passed/Failed status.
    $assessment = Assessment::factory()->create([
        'type' => AssessmentType::PostTest->value,
        'is_timed' => true,
        'time_limit_minutes' => 10,
        'passing_score_pct' => 50,
    ]);
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::MultipleChoice->value,
        'points' => 10,
    ]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $correctChoice->id);

    // Simulate the time limit having elapsed since the attempt started.
    TestAttempt::where('user_id', $user->id)
        ->where('assessment_id', $assessment->id)
        ->update(['started_at' => now()->subMinutes(11)]);

    $component->call('checkTimeExpired')
        ->assertSet('isFinished', true)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::Passed;
        });
});

test('an unanswered timed assessment is auto-graded as failed when time expires', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    // Pinned to a non-pre-test type: pre-tests never get a Passed/Failed status.
    $assessment = Assessment::factory()->create([
        'type' => AssessmentType::PostTest->value,
        'is_timed' => true,
        'time_limit_minutes' => 5,
        'passing_score_pct' => 50,
    ]);
    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::MultipleChoice->value,
        'points' => 10,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment]);

    TestAttempt::where('user_id', $user->id)
        ->where('assessment_id', $assessment->id)
        ->update(['started_at' => now()->subMinutes(6)]);

    $component->call('checkTimeExpired')
        ->assertSet('isFinished', true)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::Failed;
        });
});

test('revision retries are capped by the assessment max_attempts', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['max_attempts' => 1]);
    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::RevisionNeeded->value,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->assertSet('isFinished', true)
        ->call('retryAttempt')
        ->assertSet('currentAttempt.attempt_number', 1);

    expect(TestAttempt::where('assessment_id', $assessment->id)->count())->toBe(1);
});
