<?php

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\TestAttempt;
use App\Models\User;

test('video content is completed once its content view is marked complete', function () {
    $user = User::factory()->create();
    $content = ModuleContent::factory()->create(['content_type' => ContentType::Video]);

    expect($content->isCompletedFor($user))->toBeFalse();

    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    $content->refresh();

    expect($content->isCompletedFor($user))->toBeTrue();
});

test('document content is completed once its content view is marked complete', function () {
    $user = User::factory()->create();
    $content = ModuleContent::factory()->create(['content_type' => ContentType::Document]);

    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    $content->refresh();

    expect($content->isCompletedFor($user))->toBeTrue();
});

test('link content is completed once its content view is marked complete', function () {
    $user = User::factory()->create();
    $content = ModuleContent::factory()->create(['content_type' => ContentType::Link]);

    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    $content->refresh();

    expect($content->isCompletedFor($user))->toBeTrue();
});

test('test content is completed only once the linked assessment has a passed attempt', function () {
    $user = User::factory()->create();
    $assessment = Assessment::factory()->create(['type' => AssessmentType::ModuleTest]);
    $content = ModuleContent::factory()->create([
        'content_type' => ContentType::Test,
        'assessment_id' => $assessment->id,
    ]);

    expect($content->isCompletedFor($user))->toBeFalse();

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Failed,
    ]);

    expect($content->isCompletedFor($user))->toBeFalse();

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 2,
        'status' => TestAttemptStatus::Passed,
    ]);

    expect($content->isCompletedFor($user))->toBeTrue();
});

test('test content with no linked assessment is never completed', function () {
    $user = User::factory()->create();
    $content = ModuleContent::factory()->create(['content_type' => ContentType::Test, 'assessment_id' => null]);

    expect($content->isCompletedFor($user))->toBeFalse();
});

test('content in a non-sequential module is always accessible', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_sequential' => false]);

    $first = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);
    $second = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 2]);

    $module->load('contents.views');

    expect($first->isAccessibleFor($user))->toBeTrue()
        ->and($second->isAccessibleFor($user))->toBeTrue();
});

test('content in a sequential module is locked until prior content is completed', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_sequential' => true]);

    $first = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);
    $second = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 2]);

    $module->load('contents.views');

    expect($second->isAccessibleFor($user))->toBeFalse();

    $first->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);
    $module->load('contents.views');
    $second = $module->contents->firstWhere('id', $second->id);

    expect($second->isAccessibleFor($user))->toBeTrue();
});

test('a test-type predecessor gates the next sequential content until its assessment is passed', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_sequential' => true]);
    $assessment = Assessment::factory()->create(['type' => AssessmentType::ModuleTest]);

    $testContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Test,
        'assessment_id' => $assessment->id,
        'sort_order' => 1,
    ]);
    $next = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 2]);

    $module->load('contents.views', 'contents.assessment.attempts');
    $next = $module->contents->firstWhere('id', $next->id);

    expect($next->isAccessibleFor($user))->toBeFalse();

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Passed,
    ]);

    $module->load('contents.views', 'contents.assessment.attempts');
    $next = $module->contents->firstWhere('id', $next->id);

    expect($next->isAccessibleFor($user))->toBeTrue();
});
