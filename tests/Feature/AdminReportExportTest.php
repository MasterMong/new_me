<?php

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Reports\Index;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Livewire\Livewire;

test('admin can export the course completion report as csv', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('exportCsv')
        ->assertFileDownloaded('course-completion-report-'.now()->format('Y-m-d').'.csv');
});

test('the exported csv contains accurate course completion data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create(['title' => 'หลักสูตรทดสอบ Export']);

    $completedLearner = User::factory()->create(['role' => UserRole::Learner->value]);
    Enrollment::factory()->create([
        'user_id' => $completedLearner->id,
        'course_id' => $course->id,
        'status' => EnrollmentStatus::Certified->value,
    ]);
    Certificate::factory()->create(['user_id' => $completedLearner->id, 'course_id' => $course->id]);

    $inProgressLearner = User::factory()->create(['role' => UserRole::Learner->value]);
    Enrollment::factory()->create([
        'user_id' => $inProgressLearner->id,
        'course_id' => $course->id,
        'status' => EnrollmentStatus::InProgress->value,
    ]);

    $this->actingAs($admin);

    $response = Livewire::test(Index::class)->call('exportCsv');

    $csv = base64_decode(data_get($response->effects, 'download.content'));

    expect($csv)->toContain('หลักสูตรทดสอบ Export');
    expect($csv)->toContain('50'); // 1 of 2 enrollments completed = 50%
});
