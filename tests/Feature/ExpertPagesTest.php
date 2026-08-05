<?php

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\TestAttempt;
use App\Models\User;

test('expert can view dashboard', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);

    $response = $this->actingAs($expert)->get(route('expert.dashboard'));

    $response->assertOk();
    $response->assertSee('แดชบอร์ดผู้เชี่ยวชาญ');
});

test('expert can view submissions list for a module', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    $response = $this->actingAs($expert)->get(route('expert.submissions.index', $module->id));

    $response->assertOk();
    $response->assertSee($module->title);
});

test('expert can view review submission page', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($expert)->get(route('expert.submissions.review', $attempt->id));

    $response->assertOk();
    $response->assertSee('ประเมินผล');
});

test('learners cannot access expert pages', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);

    $response = $this->actingAs($learner)->get(route('expert.dashboard'));

    $response->assertRedirect('/');
});

test('assigned expert can view submissions list for a module once assignments exist', function () {
    $assignedExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $otherExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $assignedExpert->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($assignedExpert)
        ->get(route('expert.submissions.index', $module->id))
        ->assertOk();

    $this->actingAs($otherExpert)
        ->get(route('expert.submissions.index', $module->id))
        ->assertForbidden();
});

test('unassigned expert cannot view review submission page once module has assignments', function () {
    $assignedExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $otherExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $assignedExpert->id,
        'assigned_at' => now(),
    ]);

    $attempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($assignedExpert)
        ->get(route('expert.submissions.review', $attempt->id))
        ->assertOk();

    $this->actingAs($otherExpert)
        ->get(route('expert.submissions.review', $attempt->id))
        ->assertForbidden();
});

test('expert dashboard only lists modules assigned to them once a module has assignments', function () {
    $assignedExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $otherExpert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $restrictedModule = Module::factory()->create([
        'course_id' => $course->id,
        'requires_expert_review' => true,
        'title' => 'Restricted Module',
    ]);
    $openModule = Module::factory()->create([
        'course_id' => $course->id,
        'requires_expert_review' => true,
        'title' => 'Open Module',
    ]);

    ModuleExpertAssignment::create([
        'module_id' => $restrictedModule->id,
        'expert_id' => $assignedExpert->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($otherExpert)
        ->get(route('expert.dashboard'))
        ->assertOk()
        ->assertDontSee('Restricted Module')
        ->assertSee('Open Module');
});
