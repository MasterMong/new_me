<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Livewire\Learner\Results;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\TestAttempt;
use App\Models\User;

test('results page is displayed for learners', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    $response->assertSee('ผลการเรียนรู้');
    $response->assertSee($course->title);
});

test('results page can download pdf', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['title' => 'Test Course']);
    $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    // Use Livewire testing approach for calling the download
    Livewire\Livewire::actingAs($user)
        ->test(Results::class)
        ->call('downloadPdf', $enrollment->id)
        ->assertFileDownloaded('ผลการเรียนรู้_Test Course.pdf');
});

test('results page shows the post-test row and a green passed badge once the post-test is passed', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
        'passing_score_pct' => 60,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    // Before the fix, $course->assessments->where('type', 'post_test') never
    // matched (loose comparison between the AssessmentType enum and a raw
    // string is always false), so this row never rendered at all.
    $response->assertSee('แบบทดสอบหลังเรียน (Post-test)');
    $response->assertSee('80%');
    $response->assertSee('ผ่านเกณฑ์');
    // color="green" resolves to Flux's green pill classes; the old
    // variant="success" prop (which Flux ignores) always fell back to gray.
    $response->assertSee('bg-green-400/20', false);
});

test('results page shows a red failed badge when the post-test score is below the passing score', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
        'passing_score_pct' => 60,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Failed->value,
        'score_pct' => 40,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    $response->assertSee('ไม่ผ่านเกณฑ์');
    $response->assertSee('bg-red-400/20', false);
});

test('results page shows the module post-test score, not an unrelated assignment', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $module = Module::factory()->create(['course_id' => $course->id, 'module_number' => 1]);

    // An assignment on the module that should NOT be picked as "the module test".
    $assignment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::Assignment->value,
        'passing_score_pct' => 60,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $assignment->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 20,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    // The module's actual post-test, which should be picked instead.
    $moduleTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PostTest->value,
        'passing_score_pct' => 60,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $moduleTest->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 90,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    $response->assertSee('90%');
    $response->assertDontSee('20%');
});

test('results page shows the pre-test row once the pre-test is taken', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $preTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PreTest->value,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $preTest->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 70,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    $response->assertSee('แบบทดสอบก่อนเรียน (Pre-test)');
    $response->assertSee('70%');
});
