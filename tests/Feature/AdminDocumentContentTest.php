<?php

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Modules;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('admin can create a document content item with rich-text body and no file url', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Document->value)
        ->set('contentTitle', 'ใบความรู้ที่ 1.1')
        ->set('contentBody', '<h3>หัวข้อ</h3><p>เนื้อหา</p>')
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', [
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'title' => 'ใบความรู้ที่ 1.1',
        'body' => '<h3>หัวข้อ</h3><p>เนื้อหา</p>',
        'file_url' => null,
    ]);
});

test('editing an existing document loads its rich-text body into the form', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Document->value,
        'body' => '<p>เนื้อหาเดิม</p>',
    ]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openEditContent', $content->id)
        ->assertSet('contentBody', '<p>เนื้อหาเดิม</p>');
});

test('the body field is not saved for non-document content types', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Video->value)
        ->set('contentTitle', 'วิดีโอ')
        ->set('contentFileUrl', 'https://example.com/video.mp4')
        ->set('contentBody', '<p>ไม่ควรถูกบันทึก</p>')
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', [
        'title' => 'วิดีโอ',
        'body' => null,
    ]);
});

test('uploading an image for the knowledge sheet editor stores it and dispatches its url', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Document->value)
        ->set('contentBodyImage', UploadedFile::fake()->image('diagram.png'))
        ->assertDispatched('content-image-uploaded')
        ->assertSet('contentBodyImage', null);

    Storage::disk('public')->assertExists(
        collect(Storage::disk('public')->allFiles('course-content/knowledge-sheet-images'))->first()
    );
});
