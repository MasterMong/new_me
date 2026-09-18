<?php

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Modules;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Livewire\Livewire;

test('admin can create a worksheet content item with a file url and answer key url', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Worksheet->value)
        ->set('contentTitle', 'ใบงานที่ 1.1')
        ->set('contentFileUrl', 'https://example.com/worksheet.pdf')
        ->set('contentAnswerKeyUrl', 'https://example.com/answer-key.pdf')
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', [
        'module_id' => $module->id,
        'content_type' => ContentType::Worksheet->value,
        'title' => 'ใบงานที่ 1.1',
        'file_url' => 'https://example.com/worksheet.pdf',
        'answer_key_url' => 'https://example.com/answer-key.pdf',
    ]);
});

test('editing an existing worksheet loads its answer key url into the form', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create([
        'module_id' => $module->id,
        'content_type' => ContentType::Worksheet->value,
        'file_url' => 'https://example.com/worksheet.pdf',
        'answer_key_url' => 'https://example.com/answer-key.pdf',
    ]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openEditContent', $content->id)
        ->assertSet('contentAnswerKeyUrl', 'https://example.com/answer-key.pdf');
});

test('the answer key url is not saved for non-worksheet content types', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openCreateContent', $module->id)
        ->set('contentType', ContentType::Document->value)
        ->set('contentTitle', 'เอกสารประกอบ')
        ->set('contentFileUrl', 'https://example.com/doc.pdf')
        ->set('contentAnswerKeyUrl', 'https://example.com/should-be-ignored.pdf')
        ->call('saveContent')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('module_contents', [
        'title' => 'เอกสารประกอบ',
        'answer_key_url' => null,
    ]);
});
