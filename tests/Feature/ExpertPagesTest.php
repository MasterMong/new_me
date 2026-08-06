<?php

use App\Enums\ExpertReviewStatus;
use App\Enums\UserExperience;
use App\Enums\UserRole;
use App\Livewire\Expert\Dashboard;
use App\Models\Affiliation;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\ExpertReview;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\Position;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

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

test('expert can view reports index', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);

    $this->actingAs($expert)
        ->get(route('expert.reports.index'))
        ->assertOk();
});

test('expert can view individual learner report', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);

    // Regression test: IndividualReport::mount() used to compare the role
    // enum against a raw string ($user->role === 'learner'), which is always
    // false, so this page 404'd for every real learner before the fix.
    $this->actingAs($expert)
        ->get(route('expert.reports.show', $learner->id))
        ->assertOk();
});

test('expert can view settings page', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);

    $this->actingAs($expert)
        ->get(route('expert.settings'))
        ->assertOk();
});

test('expert sidebar links to expert settings and has no dead links', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);

    $response = $this->actingAs($expert)->get(route('expert.dashboard'));

    $response->assertOk();
    $response->assertSee(route('expert.settings'), false);
    $response->assertDontSee('href="#"', false);
});

test('overdue submission shows a waiting-days badge on the submissions list', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now()->subDays(5),
        'submitted_at' => now()->subDays(5),
    ]);

    $this->actingAs($expert)
        ->get(route('expert.submissions.index', $module->id))
        ->assertOk()
        ->assertSee('รอมาแล้ว 5 วัน');
});

test('expert dashboard counts overdue pending reviews', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'pending_review',
        'started_at' => now()->subDays(5),
        'submitted_at' => now()->subDays(5),
    ]);
    TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 2,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $this->actingAs($expert);

    Livewire::test(Dashboard::class)
        ->assertViewHas('totalOverdue', 1);
});

test('review page shows learner position, affiliation, and experience', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $position = Position::factory()->create(['name' => 'ครูชำนาญการ']);
    $affiliation = Affiliation::factory()->create(['name' => 'สพท.ทดสอบ เขต 1']);
    $learner = User::factory()->create([
        'role' => UserRole::Learner->value,
        'position_id' => $position->id,
        'affiliation_id' => $affiliation->id,
        'experience' => UserExperience::TwoToFive->value,
    ]);
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
    $response->assertSee('ครูชำนาญการ');
    $response->assertSee('สพท.ทดสอบ เขต 1');
    $response->assertSee('2 - 5 ปี');
});

test('review page shows previous attempt feedback on resubmission', function () {
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);
    $assessment = Assessment::factory()->create(['module_id' => $module->id, 'requires_expert_review' => true]);

    $firstAttempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 1,
        'status' => 'revision_needed',
        'started_at' => now()->subDays(2),
        'submitted_at' => now()->subDays(2),
    ]);

    ExpertReview::create([
        'attempt_id' => $firstAttempt->id,
        'expert_id' => $expert->id,
        'status' => ExpertReviewStatus::RevisionNeeded->value,
        'feedback' => 'กรุณาแก้ไขคำตอบข้อ 2 ให้ละเอียดขึ้น',
        'reviewed_at' => now()->subDays(1),
    ]);

    $secondAttempt = TestAttempt::create([
        'user_id' => $learner->id,
        'assessment_id' => $assessment->id,
        'attempt_number' => 2,
        'status' => 'pending_review',
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($expert)->get(route('expert.submissions.review', $secondAttempt->id));

    $response->assertOk();
    $response->assertSee('ประวัติการส่งครั้งก่อนหน้า');
    $response->assertSee('กรุณาแก้ไขคำตอบข้อ 2 ให้ละเอียดขึ้น');
});

test('review page hides previous-attempt history section on a first attempt', function () {
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
    $response->assertDontSee('ประวัติการส่งครั้งก่อนหน้า');
});
