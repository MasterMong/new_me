<?php

use App\Enums\UserRole;
use App\Livewire\Learner\MyProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Livewire;

test('unauthenticated users cannot access personal progress', function () {
    $this->get(route('learn.progress'))
        ->assertRedirect(route('login'));
});

test('learners can access their personal progress', function () {
    $user = User::factory()->create(['role' => UserRole::Learner]);
    
    $this->actingAs($user)
        ->get(route('learn.progress'))
        ->assertOk()
        ->assertSee('สรุปความก้าวหน้า');
});

test('my progress page shows correct stats', function () {
    $user = User::factory()->create(['role' => UserRole::Learner]);
    $courses = Course::factory()->count(3)->create();
    
    // Enroll in 3 courses
    foreach ($courses as $course) {
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);
    }

    $this->actingAs($user);

    Livewire::test(MyProgress::class)
        ->assertViewHas('stats', function ($stats) {
            return $stats['total_enrolled'] === 3 && $stats['completed'] === 0;
        })
        ->assertSee($courses[0]->title);
});
