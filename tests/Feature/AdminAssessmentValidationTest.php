<?php

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Livewire\Admin\Courses\Modules;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('an assessment from a different course cannot be linked to a test content item', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $otherCourse = Course::factory()->create();
    $foreignAssessment = Assessment::factory()->create(['course_id' => $otherCourse->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Test->value)
        ->set('contentTitle', 'แบบทดสอบ')
        ->set('contentAssessmentId', $foreignAssessment->id)
        ->call('saveContent')
        ->assertHasErrors('contentAssessmentId');

    $this->assertDatabaseMissing('module_contents', ['title' => 'แบบทดสอบ']);
});

test('an assessment from the same course can be linked to a test content item', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Test->value)
        ->set('contentTitle', 'แบบทดสอบ')
        ->set('contentAssessmentId', $assessment->id)
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', ['title' => 'แบบทดสอบ', 'assessment_id' => $assessment->id]);
});

test('an out-of-range grading mode is rejected with a validation error instead of a 500', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบ')
        ->set('assessmentGradingMode', 'not-a-real-mode')
        ->call('saveAssessment')
        ->assertHasErrors('assessmentGradingMode');
});

test('an out-of-range question type is rejected with a validation error instead of a 500', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('openCreateQuestion')
        ->set('questionText', 'คำถามทดสอบ')
        ->set('questionType', 'not-a-real-type')
        ->call('saveQuestion')
        ->assertHasErrors('questionType');
});

test('a multiple-choice question must have at least one correct choice', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('openCreateQuestion')
        ->set('questionText', 'คำถามทดสอบ')
        ->set('choices', [
            ['text' => 'ตัวเลือก 1', 'is_correct' => false],
            ['text' => 'ตัวเลือก 2', 'is_correct' => false],
        ])
        ->call('saveQuestion')
        ->assertHasErrors('choices');

    $this->assertDatabaseMissing('questions', ['question_text' => 'คำถามทดสอบ']);
});

test('a multiple-choice question with a correct choice saves fine', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('manageQuestions', $assessment->id)
        ->call('openCreateQuestion')
        ->set('questionText', 'คำถามทดสอบ')
        ->set('choices', [
            ['text' => 'ตัวเลือก 1', 'is_correct' => true],
            ['text' => 'ตัวเลือก 2', 'is_correct' => false],
        ])
        ->call('saveQuestion')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('questions', ['question_text' => 'คำถามทดสอบ']);
});

test('deleting an already-deleted assessment raises a model-not-found instead of a fatal null-method-call error', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    $this->expectException(ModelNotFoundException::class);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('deleteAssessment', 999999);
});

test('deleting an already-deleted question raises a model-not-found instead of a fatal null-method-call error', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    $this->expectException(ModelNotFoundException::class);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('deleteQuestion', 999999);
});
