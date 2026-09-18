<?php

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Learner\CoursePlayer;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Livewire\Livewire;

test('a document with a rich-text body renders that content instead of the pdf iframe', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'body' => '<h3>หัวข้อทดสอบ</h3><p>เนื้อหาทดสอบ</p>',
        'file_url' => null,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $content])
        ->assertSee('หัวข้อทดสอบ', false)
        ->assertSee('เนื้อหาทดสอบ', false)
        ->assertDontSee('<iframe', false);
});

test('the content player container clips overflow so a long body scrolls instead of silently overflowing', function () {
    // Regression: the shared content-player container centers its children
    // (items-center), which without `overflow-hidden` lets flexbox's
    // automatic-minimum-size algorithm force it to grow to fit a tall
    // rich-text body — overriding its flex-1 sizing entirely, so the excess
    // content becomes invisible (clipped by the page's own overflow-hidden
    // root) with no way to scroll to it.
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'body' => str_repeat('<p>เนื้อหา</p>', 500),
        'file_url' => null,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    $html = Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $content])->html();

    expect($html)->toContain('flex-1 flex items-center justify-center bg-black relative overflow-hidden');
});

test('a document with no body falls back to the file url iframe', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'body' => null,
        'file_url' => 'https://example.com/handout.pdf',
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $content])
        ->assertSee('https://example.com/handout.pdf', false);
});

test('the open-in-new-tab button is hidden for a body-only document with no file url', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'body' => '<p>เนื้อหา</p>',
        'file_url' => null,
    ]);

    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $this->actingAs($user);

    Livewire::test(CoursePlayer::class, ['course' => $course, 'module' => $module, 'content' => $content])
        ->assertDontSee('เปิดในแท็บใหม่');
});
