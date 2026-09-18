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
