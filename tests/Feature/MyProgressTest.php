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

test('course list is actually sorted by latest enrollment as the badge claims', function () {
    $user = User::factory()->create(['role' => UserRole::Learner]);
    $older = Course::factory()->create(['title' => 'Older Course']);
    $newer = Course::factory()->create(['title' => 'Newer Course']);

    Enrollment::create(['user_id' => $user->id, 'course_id' => $older->id, 'enrolled_at' => now()->subDays(10)]);
    Enrollment::create(['user_id' => $user->id, 'course_id' => $newer->id, 'enrolled_at' => now()]);

    $this->actingAs($user);

    Livewire::test(MyProgress::class)
        ->assertViewHas('enrollments', function ($enrollments) use ($newer, $older) {
            return $enrollments->first()->course_id === $newer->id
                && $enrollments->last()->course_id === $older->id;
        });
});

test('continue button links to the learner course page, not the public course page', function () {
    $user = User::factory()->create(['role' => UserRole::Learner]);
    $course = Course::factory()->create();
    Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'enrolled_at' => now()]);

    $response = $this->actingAs($user)->get(route('learn.progress'));

    $response->assertOk();
    // Before the fix this linked to route('courses.show', ...), the public
    // marketing page, whose visibility check is independent of enrollment.
    $response->assertSee(route('learn.courses.show', $course), false);
});
