<?php

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;

test('course review page can be loaded', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $response = $this->actingAs($user)->get('/learn/courses/'.$course->id.'/review');

    $response->assertOk();
    $response->assertSee('แบบประเมินความพึงพอใจ');
});

test('learner who is not enrolled cannot access the review page', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();

    $response = $this->actingAs($user)->get('/learn/courses/'.$course->id.'/review');

    $response->assertForbidden();
});

test('can submit a course review', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    Livewire\Livewire::actingAs($user)
        ->test(App\Livewire\Learner\CourseReview::class, ['course' => $course])
        ->set('rating', 4)
        ->set('comment', 'Very good course!')
        ->call('submitReview')
        ->assertRedirect(route('learn.certificates.index'));

    $this->assertDatabaseHas('course_reviews', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'rating' => 4,
        'comment' => 'Very good course!',
    ]);
});

test('redirects if user already reviewed the course', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create();
    Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    CourseReview::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'rating' => 5,
    ]);

    $response = $this->actingAs($user)->get('/learn/courses/'.$course->id.'/review');

    $response->assertRedirect(route('learn.certificates.index'));
});
