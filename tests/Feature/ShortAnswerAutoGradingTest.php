<?php

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\TestAnswer;
use App\Models\User;
use Livewire\Livewire;

test('correct_answer is actually persisted when creating a question', function () {
    $question = Question::create([
        'assessment_id' => Assessment::factory()->create()->id,
        'question_type' => QuestionType::ShortAnswer->value,
        'question_text' => 'PLC ย่อมาจากอะไร?',
        'correct_answer' => 'Professional Learning Community',
        'points' => 5,
        'grading_mode' => GradingMode::Auto->value,
        'sort_order' => 1,
    ]);

    expect($question->fresh()->correct_answer)->toBe('Professional Learning Community');
});

test('admin creating a short_answer question is forced into auto grading and requires a correct answer', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    // Missing correct answer is rejected.
    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('openCreateQuestion')
        ->set('questionType', 'short_answer')
        ->set('questionText', 'PLC ย่อมาจากอะไร?')
        ->set('questionCorrectAnswer', '')
        ->call('saveQuestion')
        ->assertHasErrors(['questionCorrectAnswer']);

    // With a correct answer supplied, it saves as auto-graded.
    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('openCreateQuestion')
        ->set('questionType', 'short_answer')
        ->set('questionText', 'PLC ย่อมาจากอะไร?')
        ->set('questionCorrectAnswer', 'Professional Learning Community')
        ->call('saveQuestion');

    $this->assertDatabaseHas('questions', [
        'assessment_id' => $assessment->id,
        'question_type' => 'short_answer',
        'correct_answer' => 'Professional Learning Community',
        'grading_mode' => 'auto',
    ]);
});

test('a correct short answer is auto-graded as full points', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::ShortAnswer->value,
        'grading_mode' => GradingMode::Auto->value,
        'correct_answer' => 'Professional Learning Community',
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, '  professional learning community  ')
        ->call('finish')
        ->assertSet('isFinished', true)
        ->assertSet('score', 100)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::Passed;
        });

    $this->assertDatabaseHas('test_answers', [
        'question_id' => $question->id,
        'is_correct' => true,
        'score' => 10,
    ]);
});

test('an incorrect short answer is auto-graded as zero points', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::PostTest->value, 'passing_score_pct' => 50]);
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::ShortAnswer->value,
        'grading_mode' => GradingMode::Auto->value,
        'correct_answer' => 'Professional Learning Community',
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, 'wrong answer')
        ->call('finish')
        ->assertSet('score', 0)
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status === TestAttemptStatus::Failed;
        });

    $this->assertDatabaseHas('test_answers', [
        'question_id' => $question->id,
        'is_correct' => false,
        'score' => 0,
    ]);
});

test('an assessment made only of auto-graded short answer questions does not require expert review', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['passing_score_pct' => 50]);
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::ShortAnswer->value,
        'grading_mode' => GradingMode::Auto->value,
        'correct_answer' => 'Specific',
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, 'Specific')
        ->call('finish')
        ->assertViewHas('currentAttempt', function ($attempt) {
            return $attempt->status !== TestAttemptStatus::PendingReview;
        });
});

test('a manually-graded short answer question is not auto-scored', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create();
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::ShortAnswer->value,
        'grading_mode' => GradingMode::Manual->value,
        'correct_answer' => null,
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.$question->id, 'my answer')
        ->call('saveDraft');

    $answer = TestAnswer::where('question_id', $question->id)->first();

    expect($answer->essay_text)->toBe('my answer');
    expect($answer->score)->toBeNull();
    expect($answer->is_correct)->toBeNull();
});
