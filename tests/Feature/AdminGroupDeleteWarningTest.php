<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Groups\Index;
use App\Models\ContentGroupAccess;
use App\Models\Course;
use App\Models\CourseGroupAccess;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\User;
use Livewire\Livewire;

test('group delete confirmation warns about the number of restricted courses and content items', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $group = LearnerGroup::factory()->create(['name' => 'กลุ่มทดสอบ']);

    $course = Course::factory()->create();
    CourseGroupAccess::create(['course_id' => $course->id, 'group_id' => $group->id]);

    $module = Module::factory()->create(['course_id' => $course->id]);
    $content = ModuleContent::factory()->create(['module_id' => $module->id]);
    ContentGroupAccess::create(['content_id' => $content->id, 'group_id' => $group->id]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertSee('ใช้จำกัดสิทธิ์เข้าถึงคอร์ส/เนื้อหาอยู่ 2 รายการ');
});

test('group delete confirmation stays generic when the group has no restrictions', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    LearnerGroup::factory()->create(['name' => 'กลุ่มว่าง']);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertDontSee('ใช้จำกัดสิทธิ์เข้าถึงคอร์ส/เนื้อหาอยู่');
});
