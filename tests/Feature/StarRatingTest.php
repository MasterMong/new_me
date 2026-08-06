<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('the first attempt is worth the full star quota', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    // Pinned to a non-pre-test type: star ratings don't apply to pre-tests.
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment]);

    $this->assertDatabaseHas('test_attempts', [
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'star_rating' => 3,
    ]);
});

test('each retry is worth one fewer star, down to a floor of 1', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3, 'passing_score_pct' => 100]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish');

    $this->assertDatabaseHas('test_attempts', ['attempt_number' => 1, 'star_rating' => 3]);

    $component->call('retryAttempt')
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish');

    $this->assertDatabaseHas('test_attempts', ['attempt_number' => 2, 'star_rating' => 2]);

    $component->call('retryAttempt')
        ->call('selectChoice', $correctChoice->id)
        ->call('finish');

    $this->assertDatabaseHas('test_attempts', ['attempt_number' => 3, 'star_rating' => 1]);
});

test('the star value scales with a custom max_attempts', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 5]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment]);

    $this->assertDatabaseHas('test_attempts', [
        'attempt_number' => 1,
        'star_rating' => 5,
    ]);
});

test('starRatingForNextAttempt reflects the star value a retry would earn', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3, 'passing_score_pct' => 100]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);
    QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish')
        ->assertSee('ครั้งถัดไปจะได้สูงสุด 2 ดาว');
});

test('a revised worksheet attempt keeps the star value it was created with once passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3]);

    // First attempt already used up (sent back for revision).
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'star_rating' => 3,
        'status' => TestAttemptStatus::RevisionNeeded->value,
    ]);

    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    // Loading the assessment player again should show the second attempt's outcome (revision),
    // and retrying should create attempt 2 worth one fewer star.
    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('retryAttempt');

    $this->assertDatabaseHas('test_attempts', [
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 2,
        'star_rating' => 2,
    ]);
});

test('a failed attempt does not display filled stars even though star_rating is set', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3, 'passing_score_pct' => 60]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $wrongChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => false]);
    QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $wrongChoice->id)
        ->call('finish');

    // star_rating is still stored on the failed attempt (it's the "if you
    // pass" quota, not an earned rating) — the results screen must not
    // render it as filled/achieved stars next to "ไม่ผ่านเกณฑ์".
    $this->assertDatabaseHas('test_attempts', ['attempt_number' => 1, 'star_rating' => 3, 'status' => TestAttemptStatus::Failed->value]);
    $component->assertSee('ไม่ผ่านเกณฑ์')->assertDontSee('text-secondary', false);
});

test('a passed attempt displays its earned stars', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'max_attempts' => 3, 'passing_score_pct' => 60]);
    $question = Question::factory()->create(['assessment_id' => $assessment->id, 'points' => 10]);
    $correctChoice = QuestionChoice::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user);

    $component = Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->call('selectChoice', $correctChoice->id)
        ->call('finish');

    $this->assertDatabaseHas('test_attempts', ['attempt_number' => 1, 'status' => TestAttemptStatus::Passed->value]);
    $component->assertSee('ผ่านเกณฑ์')->assertSee('text-secondary', false);
});
