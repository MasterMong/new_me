<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Assessments;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

test('admin can enable a time limit when creating an assessment', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบมีเวลาจำกัด')
        // openCreateAssessment() defaults assessmentType to an invalid enum value
        // ('quiz' isn't a valid AssessmentType case) — pre-existing bug, out of
        // scope here, so set a valid type explicitly to isolate the timer feature.
        ->set('assessmentType', 'module_test')
        ->set('assessmentIsTimed', true)
        ->set('assessmentTimeLimitMinutes', 45)
        ->call('saveAssessment')
        ->assertSet('showAssessmentModal', false);

    $this->assertDatabaseHas('assessments', [
        'title' => 'แบบทดสอบมีเวลาจำกัด',
        'is_timed' => true,
        'time_limit_minutes' => 45,
    ]);
});

test('time limit is required when the timer is enabled', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('openCreateAssessment')
        ->set('assessmentTitle', 'แบบทดสอบ')
        ->set('assessmentIsTimed', true)
        ->set('assessmentTimeLimitMinutes', null)
        ->call('saveAssessment')
        ->assertHasErrors(['assessmentTimeLimitMinutes']);
});

test('disabling the timer clears the stored time limit', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'is_timed' => true,
        'time_limit_minutes' => 30,
    ]);

    $this->actingAs($admin);

    Livewire::test(Assessments::class, ['course' => $course])
        ->call('editAssessment', $assessment->id)
        ->assertSet('assessmentIsTimed', true)
        ->assertSet('assessmentTimeLimitMinutes', 30)
        ->set('assessmentIsTimed', false)
        ->call('saveAssessment');

    $this->assertDatabaseHas('assessments', [
        'id' => $assessment->id,
        'is_timed' => false,
        'time_limit_minutes' => null,
    ]);
});
