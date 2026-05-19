<?php

use App\Enums\UserRole;
use App\Livewire\Learner\Certificates;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Livewire;

test('learner can view their certificates and ongoing courses', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course1 = Course::factory()->create(['title' => 'Completed Course']);
    $course2 = Course::factory()->create(['title' => 'Ongoing Course']);

    // Create a certificate
    Certificate::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course1->id,
        'issued_date' => now(),
    ]);

    // Create an ongoing enrollment
    Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course2->id,
        'completed_at' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Certificates::class)
        ->assertSee('Completed Course')
        ->assertSee('Ongoing Course')
        ->assertSee('1') // Total earned
        ->assertSee('ได้รับแล้ว');
});

test('empty states are shown when no achievements', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $this->actingAs($user);

    Livewire::test(Certificates::class)
        ->assertSee('ยังไม่มีเกียรติบัตร')
        ->assertSee('ไม่มีคอร์สที่กำลังเรียนอยู่ในขณะนี้');
});
