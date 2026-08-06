<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\CertificateTemplate;
use App\Livewire\Admin\Courses\Edit as AdminCoursesEdit;
use App\Livewire\Admin\Courses\Modules as AdminCourseModules;
use App\Models\Course;
use App\Models\CourseImage;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('replacing a certificate template image deletes the old file from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('certificates/templates', 'public');
    $oldUrl = Storage::disk('public')->url($oldPath);
    $course->certificateTemplate()->create([
        'template_image_url' => $oldUrl,
        'name_x' => 0, 'name_y' => 0, 'date_x' => 0, 'date_y' => 0,
    ]);

    $this->actingAs($admin);

    Livewire::test(CertificateTemplate::class, ['course' => $course])
        ->set('templateImage', UploadedFile::fake()->image('new.jpg'))
        ->call('save');

    Storage::disk('public')->assertMissing($oldPath);
});

test('replacing a course thumbnail deletes the old file from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('courses/thumbnails', 'public');
    $course = Course::factory()->create(['thumbnail_url' => Storage::disk('public')->url($oldPath)]);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesEdit::class, ['course' => $course])
        ->set('thumbnail', UploadedFile::fake()->image('new.jpg'))
        ->call('save');

    Storage::disk('public')->assertMissing($oldPath);
});

test('removing a gallery image deletes the file from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $path = UploadedFile::fake()->image('gallery.jpg')->store('courses/gallery', 'public');
    $image = CourseImage::create([
        'course_id' => $course->id,
        'image_url' => Storage::disk('public')->url($path),
        'sort_order' => 10,
    ]);

    $this->actingAs($admin);

    Livewire::test(AdminCoursesEdit::class, ['course' => $course])
        ->call('removeGalleryImage', $image->id);

    Storage::disk('public')->assertMissing($path);
});

test('replacing a module thumbnail deletes the old file from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('modules/thumbnails', 'public');
    $module = Module::factory()->create([
        'course_id' => $course->id,
        'thumbnail_url' => Storage::disk('public')->url($oldPath),
    ]);

    $this->actingAs($admin);

    Livewire::test(AdminCourseModules::class, ['course' => $course])
        ->call('openEditModule', $module->id)
        ->set('moduleThumbnail', UploadedFile::fake()->image('new.jpg'))
        ->call('saveModule');

    Storage::disk('public')->assertMissing($oldPath);
});
