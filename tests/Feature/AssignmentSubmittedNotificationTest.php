<?php

use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Livewire\Learner\AssessmentPlayer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\Question;
use App\Models\User;
use App\Notifications\AssignmentSubmitted;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('submitting a worksheet notifies the experts assigned to that module', function () {
    Notification::fake();

    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $assignedExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $otherExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'module_id' => $module->id]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $assignedExpert->id,
        'assigned_at' => now(),
    ]);

    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $this->actingAs($learner);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.Question::first()->id, 'คำตอบของฉัน')
        ->call('finish');

    Notification::assertSentTo($assignedExpert, AssignmentSubmitted::class);
    Notification::assertNotSentTo($otherExpert, AssignmentSubmitted::class);
});

test('submitting a worksheet for a module open to all experts notifies no one specifically', function () {
    Notification::fake();

    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'module_id' => $module->id]);

    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $this->actingAs($learner);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.Question::first()->id, 'คำตอบของฉัน')
        ->call('finish');

    Notification::assertNothingSent();
});

test('submitting a worksheet for a course-level assessment with no module does not error', function () {
    Notification::fake();

    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $assessment = Assessment::factory()->create(['module_id' => null]);

    Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => QuestionType::Essay->value,
        'grading_mode' => GradingMode::Manual->value,
    ]);

    $this->actingAs($learner);

    Livewire::test(AssessmentPlayer::class, ['assessment' => $assessment])
        ->set('essayAnswers.'.Question::first()->id, 'คำตอบของฉัน')
        ->call('finish')
        ->assertSet('isFinished', true);

    Notification::assertNothingSent();
});
