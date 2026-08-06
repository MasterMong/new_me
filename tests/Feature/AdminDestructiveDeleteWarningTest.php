<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Edit as AdminCoursesEdit;
use App\Livewire\Admin\Courses\Index as AdminCoursesIndex;
use App\Livewire\Admin\Courses\Modules as AdminCourseModules;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use Livewire\Livewire;

test('course list delete warning mentions enrollment and certificate counts when present', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create(['title' => 'หลักสูตรมีคนเรียน']);
    Enrollment::factory()->create(['course_id' => $course->id]);
    Certificate::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesIndex::class)
        ->assertSee('มีผู้ลงทะเบียน 1 คน และเกียรติบัตรที่ออกแล้ว 1 ใบ');
});

test('course list delete warning stays generic for a course with no enrollments', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    Course::factory()->create(['title' => 'หลักสูตรว่าง']);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesIndex::class)
        ->assertDontSee('มีผู้ลงทะเบียน');
});

test('course edit delete warning mentions enrollment and certificate counts', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create(['title' => 'หลักสูตรมีคนเรียน']);
    Enrollment::factory()->create(['course_id' => $course->id]);
    Certificate::factory()->create(['course_id' => $course->id]);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesEdit::class, ['course' => $course])
        ->assertSee('มีผู้ลงทะเบียน 1 คน และเกียรติบัตรที่ออกแล้ว 1 ใบ');
});

test('module delete warning mentions learner progress records when present', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'title' => 'โมดูลมีความก้าวหน้า']);
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    ModuleProgress::create([
        'user_id' => $learner->id,
        'module_id' => $module->id,
        'status' => 'in_progress',
    ]);

    $this->actingAs($admin);

    Livewire::test(AdminCourseModules::class, ['course' => $course])
        ->assertSee('มีข้อมูลความก้าวหน้าของผู้เรียน 1 รายการ');
});
