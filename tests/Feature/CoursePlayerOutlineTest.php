<?php

use App\Enums\AssessmentType;
use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Modules;
use App\Livewire\Learner\CoursePlayer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Livewire\Livewire;

test('the outline content item appears before the module pre-test link in the tree', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PreTest->value,
        'title' => 'Module pre-test',
    ]);

    $outline = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'title' => 'ภาพรวมโมดูลของฉัน',
        'sort_order' => 0,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $html = Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $outline])
        ->assertSee('ภาพรวมโมดูลของฉัน')
        ->assertSee('แบบทดสอบก่อนเรียนของโมดูล')
        ->html();

    expect(strpos($html, 'ภาพรวมโมดูลของฉัน'))->toBeLessThan(strpos($html, 'แบบทดสอบก่อนเรียนของโมดูล'));
});

test('outline content computes as accessible even as the first item in a sequential module', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'is_sequential' => true]);

    $outline = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'title' => 'ภาพรวมโมดูล',
        'sort_order' => 0,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $outline])
        ->assertSee('isAccessible: true', false);
});

test('a module\'s own pre-test still gates every other content type', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PreTest->value,
    ]);

    $video = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Video->value,
        'sort_order' => 1,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $this->get(route('learn.courses.play', [$course, $module, $video]))
        ->assertForbidden();
});

test('a bypassed outline view cannot be used to switch into other content before the pre-test is done', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'type' => AssessmentType::PreTest->value,
    ]);

    $outline = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'sort_order' => 0,
    ]);
    $video = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Video->value,
        'sort_order' => 1,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $outline])
        ->assertSet('activeContent.id', $outline->id)
        ->call('selectContent', $video->id)
        ->assertSet('activeContent.id', $outline->id);
});

test('viewing the outline shows the module description and its other content grouped by type', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create([
        'course_id' => $course->id,
        'description' => 'คำอธิบายโมดูลทดสอบ',
    ]);

    $outline = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'title' => 'ภาพรวมโมดูล',
        'sort_order' => 0,
    ]);
    ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Video->value,
        'title' => 'วิดีโอที่ 1',
        'sort_order' => 1,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $outline])
        ->assertSee('คำอธิบายโมดูลทดสอบ')
        ->assertSee('วิดีโอที่ 1');
});

test('a learner can mark the outline as read to unlock the next sequential content item', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'is_sequential' => true]);

    $outline = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'sort_order' => 0,
    ]);
    ModuleContent::factory()->create(['module_id' => $module->id, 'sort_order' => 1]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $outline])
        ->call('markComplete', $outline->id);

    expect($outline->fresh()->isCompletedFor($user))->toBeTrue();
});

test('admin can create an outline content item without a file url', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Outline->value)
        ->set('contentTitle', 'ภาพรวมโมดูล')
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', [
        'module_id' => $module->id,
        'content_type' => ContentType::Outline->value,
        'title' => 'ภาพรวมโมดูล',
        'file_url' => null,
    ]);
});
