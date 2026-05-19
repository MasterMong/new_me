<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

test('results page is displayed for learners', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $response = $this->actingAs($user)->get('/learn/results');

    $response->assertOk();
    $response->assertSee('ผลการเรียนรู้');
    $response->assertSee($course->title);
});

test('results page can download pdf', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['title' => 'Test Course']);
    $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    // Use Livewire testing approach for calling the download
    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Learner\Results::class)
        ->call('downloadPdf', $enrollment->id)
        ->assertFileDownloaded('ผลการเรียนรู้_Test Course.pdf');
});
