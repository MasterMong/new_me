<?php

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Livewire\Learner\Notifications;
use App\Models\LearnerNotification;
use App\Models\User;
use Livewire\Livewire;

test('learner can view their notifications', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    LearnerNotification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Notification',
        'type' => NotificationType::CertificateIssued,
    ]);

    $this->actingAs($user);

    Livewire::test(Notifications::class)
        ->assertSee('Test Notification');
});

test('learner can mark notification as read', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $notification = LearnerNotification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(Notifications::class)
        ->call('markAsRead', $notification->id);

    $this->assertTrue($notification->fresh()->is_read);
});

test('learner can mark all as read', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    LearnerNotification::factory()->count(3)->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(Notifications::class)
        ->call('markAllAsRead');

    $this->assertEquals(0, LearnerNotification::where('user_id', $user->id)->where('is_read', false)->count());
});

test('sidebar shows an unread notification count badge', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    LearnerNotification::factory()->count(2)->create(['user_id' => $user->id, 'is_read' => false]);
    LearnerNotification::factory()->create(['user_id' => $user->id, 'is_read' => true]);

    $response = $this->actingAs($user)->get(route('learn.dashboard'));

    $response->assertOk();
    $response->assertSee('>2<', false);
});

test('sidebar shows no unread badge when everything is read', function () {
    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    LearnerNotification::factory()->create(['user_id' => $user->id, 'is_read' => true]);

    $response = $this->actingAs($user)->get(route('learn.dashboard'));

    $response->assertOk();
    $response->assertDontSee('>1<', false);
});
