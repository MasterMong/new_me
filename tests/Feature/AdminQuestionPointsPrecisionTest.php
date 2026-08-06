<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('editing a question with fractional points does not silently truncate them', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);
    $question = Question::factory()->create([
        'assessment_id' => $assessment->id,
        'question_type' => 'essay',
        'points' => 1.5,
    ]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('editQuestion', $question->id)
        ->assertSet('questionPoints', '1.50')
        ->call('saveQuestion');

    expect((float) $question->fresh()->points)->toBe(1.5);
});
