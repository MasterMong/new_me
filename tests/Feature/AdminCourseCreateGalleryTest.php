<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Create;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('gallery images selected while creating a course are actually persisted', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    Livewire::test(Create::class)
        ->set('title', 'คอร์สพร้อมแกลเลอรี')
        ->set('passingScorePct', 60)
        ->set('gallery', [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ])
        ->call('save');

    $course = Course::where('title', 'คอร์สพร้อมแกลเลอรี')->firstOrFail();

    expect($course->images()->count())->toBe(2);
});
