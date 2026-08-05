<?php

use App\Enums\AssessmentType;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Livewire\Livewire;

test('admin can create a pre-test scoped to a specific module', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบก่อนเรียนโมดูล 1')
        ->set('assessmentType', AssessmentType::PreTest->value)
        ->set('assessmentModuleId', $module->id)
        ->call('saveAssessment')
        ->assertSet('showAssessmentModal', false);

    $this->assertDatabaseHas('assessments', [
        'title' => 'แบบทดสอบก่อนเรียนโมดูล 1',
        'type' => AssessmentType::PreTest->value,
        'module_id' => $module->id,
    ]);
});

test('admin can create a course-wide assessment with no module', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบทั้งคอร์ส')
        ->set('assessmentType', AssessmentType::PostTest->value)
        ->call('saveAssessment');

    $this->assertDatabaseHas('assessments', [
        'title' => 'แบบทดสอบทั้งคอร์ส',
        'module_id' => null,
    ]);
});

test('a module from a different course cannot be selected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $otherCourse = Course::factory()->create();
    $foreignModule = Module::factory()->create(['course_id' => $otherCourse->id]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบ')
        ->set('assessmentType', AssessmentType::ModuleTest->value)
        ->set('assessmentModuleId', $foreignModule->id)
        ->call('saveAssessment')
        ->assertHasErrors(['assessmentModuleId']);
});

test('editing an assessment pre-fills its module scope', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PostTest->value,
    ]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('editAssessment', $assessment->id)
        ->assertSet('assessmentModuleId', $module->id)
        ->assertSet('assessmentType', AssessmentType::PostTest->value);
});

test('an invalid assessment type is rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบ')
        ->set('assessmentType', 'quiz')
        ->call('saveAssessment')
        ->assertHasErrors(['assessmentType']);
});
