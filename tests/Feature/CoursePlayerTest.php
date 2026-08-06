<?php

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Learner\CoursePlayer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\UserGroupMembership;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('learner can access course player if enrolled', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $this->get(route('learn.courses.play', [$course, $module]))
        ->assertOk();
});

test('course player shows module contents', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'Video Lesson 1',
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('Video Lesson 1');
});

test('sequential content locks next items', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create([
        'course_id' => $course->id,
        'is_sequential' => true,
    ]);

    $content1 = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);
    $content2 = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertViewHas('contents', function ($contents) {
            return $contents->firstWhere('sort_order', 1)->is_accessible === true &&
                   $contents->firstWhere('sort_order', 2)->is_accessible === false;
        });
});

test('learner can update progress', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('updateProgress', 60, 30, true);

    $this->assertDatabaseHas('content_views', [
        'user_id' => $user->id,
        'content_id' => $content->id,
        'is_completed' => true,
        'watch_duration_sec' => 60,
    ]);
});

test('group-restricted content is hidden from learners outside the group', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $openContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'Open Lesson',
        'sort_order' => 1,
    ]);

    $restrictedContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'STOP Only Lesson',
        'sort_order' => 2,
    ]);

    $restrictedGroup = LearnerGroup::factory()->create(['name' => 'สตผ.']);
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('Open Lesson')
        ->assertDontSee('STOP Only Lesson');
});

test('group-restricted content is visible to members of that group', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $restrictedContent = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'title' => 'STOP Only Lesson',
    ]);

    $restrictedGroup = LearnerGroup::factory()->create(['name' => 'สตผ.']);
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    UserGroupMembership::create([
        'user_id' => $user->id,
        'group_id' => $restrictedGroup->id,
        'assigned_at' => now(),
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->assertSee('STOP Only Lesson');
});

test('learner cannot deep-link directly to group-restricted content', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $restrictedContent = ModuleContent::factory()->create(['module_id' => $module->id]);
    $restrictedGroup = LearnerGroup::factory()->create();
    $restrictedContent->groupAccess()->create(['group_id' => $restrictedGroup->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, [
        'course' => $course,
        'module' => $module,
        'content' => $restrictedContent,
    ])->assertStatus(403);
});

test('module progress percent reaches 100 with a completed test content item', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $video = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);
    $video->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::ModuleTest,
    ]);
    ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Test,
        'assessment_id' => $assessment->id,
        'sort_order' => 2,
    ]);

    $module->load('contents.views', 'contents.assessment.attempts');
    expect($module->progressPercentFor($user))->toBeLessThan(100);

    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $assessment->id,
        'status' => TestAttemptStatus::Passed,
    ]);

    $module->load('contents.views', 'contents.assessment.attempts');
    expect($module->progressPercentFor($user))->toBe(100);
});

test('course player tree locks a module until the previous module post-test is passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module1->id, 'content_type' => ContentType::Video]);
    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::PostTest,
    ]);

    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    ModuleContent::factory()->create(['module_id' => $module2->id, 'content_type' => ContentType::Video]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module1])
        ->assertViewHas('tree', function ($tree) use ($module2) {
            return $tree->firstWhere('id', $module2->id)->is_accessible === false;
        });
});

test('expanded module ids start with only the current module and toggle correctly', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module1->id, 'content_type' => ContentType::Video]);
    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module1])
        ->assertSet('expandedModuleIds', [$module1->id])
        ->call('toggleModule', $module2->id)
        ->assertSet('expandedModuleIds', [$module1->id, $module2->id])
        ->call('toggleModule', $module2->id)
        ->assertSet('expandedModuleIds', [$module1->id]);
});

test('test-type content renders the assessment CTA instead of an unsupported message', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::ModuleTest,
        'title' => 'แบบทดสอบท้ายบท',
    ]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Test,
        'assessment_id' => $assessment->id,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $content])
        ->assertSee('แบบทดสอบท้ายบท')
        ->assertDontSee('ไม่รองรับรูปแบบเนื้อหานี้');
});

test('learner can mark document content as complete', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Document]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('markComplete', $content->id);

    $this->assertDatabaseHas('content_views', [
        'user_id' => $user->id,
        'content_id' => $content->id,
        'is_completed' => true,
    ]);
});

test('learner can mark link content as complete', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Link]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('markComplete', $content->id);

    $this->assertDatabaseHas('content_views', [
        'user_id' => $user->id,
        'content_id' => $content->id,
        'is_completed' => true,
    ]);
});

test('marking complete is rejected for content that is not the active content', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $active = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Document, 'sort_order' => 1]);
    $other = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Document, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $active])
        ->call('markComplete', $other->id)
        ->assertStatus(403);

    $this->assertDatabaseMissing('content_views', ['content_id' => $other->id]);
});

test('marking complete is rejected for video content', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module])
        ->call('markComplete', $content->id)
        ->assertStatus(403);

    $this->assertDatabaseMissing('content_views', ['content_id' => $content->id, 'is_completed' => true]);
});

test('the module player route accepts a specific content id as a real path segment', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);
    $second = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $url = route('learn.courses.play', [$course, $module, $second]);

    // Before the fix, the route had no {content} segment, so route() appended
    // the id as a query string (?0=...) instead of a real path segment, and
    // Laravel's implicit route-model binding never resolved it.
    expect($url)->toContain("/modules/{$module->id}/{$second->id}");

    $this->actingAs($user)->get($url)->assertOk();
});

test('deep-linking to a specific content id opens exactly that content, not the module default', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);
    $second = ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $second])
        ->assertSet('activeContent.id', $second->id);
});

test('tree cross-module content links include the specific content id in the url', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    ModuleContent::factory()->create(['module_id' => $module1->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);
    $content2 = ModuleContent::factory()->create(['module_id' => $module2->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module1])
        ->assertSee(route('learn.courses.play', [$course, $module2, $content2]), false);
});

test('locked content reports isAccessible false so the client-side overlay can appear', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'is_sequential' => true]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);
    $second = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 2]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user)
        ->get(route('learn.courses.play', [$course, $module, $second]))
        ->assertOk()
        ->assertSee('isAccessible: false', false);
});

test('accessible content reports isAccessible true', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'is_sequential' => true]);
    $first = ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video, 'sort_order' => 1]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user)
        ->get(route('learn.courses.play', [$course, $module, $first]))
        ->assertOk()
        ->assertSee('isAccessible: true', false);
});

test('learner cannot open a module directly via url when the previous module post-test is not passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module1->id, 'content_type' => ContentType::Video]);
    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::PostTest->value,
    ]);

    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    ModuleContent::factory()->create(['module_id' => $module2->id, 'content_type' => ContentType::Video]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user)
        ->get(route('learn.courses.play', [$course, $module2]))
        ->assertForbidden();
});

test('learner can open a module directly once the previous module post-test is passed', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $module1 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module1->id, 'content_type' => ContentType::Video]);
    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'attempt_number' => 1,
        'status' => TestAttemptStatus::Passed->value,
        'started_at' => now(),
        'submitted_at' => now(),
    ]);

    $module2 = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 2]);
    ModuleContent::factory()->create(['module_id' => $module2->id, 'content_type' => ContentType::Video]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user)
        ->get(route('learn.courses.play', [$course, $module2]))
        ->assertOk();
});

test('learner can still open the first module directly with no gating in place', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user)
        ->get(route('learn.courses.play', [$course, $module]))
        ->assertOk();
});

test('the course pre-test is not re-queried once per module while building the tree', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'sort_order' => 1]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'content_type' => ContentType::Video]);
    Module::factory()->count(4)->create(['course_id' => $course->id]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    DB::enableQueryLog();
    $this->get(route('learn.courses.play', [$course, $module]));
    $preTestQueries = collect(DB::getQueryLog())->filter(
        fn ($q) => str_contains($q['query'], 'assessments') && in_array('pre_test', $q['bindings'], true)
    );
    DB::disableQueryLog();

    // mount() and render() each legitimately compute this once (separate
    // Livewire lifecycle phases, not scaling with module count) — that's the
    // floor. Before the fix, buildTree() additionally re-ran it once per
    // module inside render() (5 modules here), so the count scaled with the
    // course size instead of staying flat.
    expect($preTestQueries->count())->toBeLessThanOrEqual(2);
});
