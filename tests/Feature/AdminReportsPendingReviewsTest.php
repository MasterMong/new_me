<?php

use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Reports\Index;
use App\Models\ExpertReview;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Livewire;

test('pending reviews stat counts attempts actually awaiting expert review', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    TestAttempt::factory()->create(['status' => TestAttemptStatus::PendingReview]);
    TestAttempt::factory()->create(['status' => TestAttemptStatus::PendingReview]);
    TestAttempt::factory()->create(['status' => TestAttemptStatus::Passed]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertViewHas('pendingReviews', 2);
});

test('pending reviews stat is not thrown off by already-reviewed ExpertReview rows', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $attempt = TestAttempt::factory()->create(['status' => TestAttemptStatus::Passed]);
    ExpertReview::create(['attempt_id' => $attempt->id, 'expert_id' => $expert->id, 'status' => 'passed']);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertViewHas('pendingReviews', 0);
});
