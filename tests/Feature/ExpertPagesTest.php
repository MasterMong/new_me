<?php

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
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
