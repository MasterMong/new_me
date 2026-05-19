<?php

use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('learner can access assessment player', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['passing_score_pct' => 80]);
    Question::factory()->create(['assessment_id' => $assessment->id]);

    $this->actingAs($user);

    $this->get(route('learn.assessments.show', $assessment))
        ->assertOk();
});

test('learner can submit answers and see results', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['passing_score_pct' => 50]);
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
