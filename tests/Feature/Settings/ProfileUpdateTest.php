<?php

use App\Models\Affiliation;
use App\Models\Position;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $position = Position::factory()->create();
    $affiliation = Affiliation::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('prefix', 'นาง')
        ->set('first_name', 'สมศรี')
        ->set('last_name', 'มีนา')
        ->set('email', 'test@example.com')
        ->set('position_id', $position->id)
        ->set('affiliation_id', $affiliation->id)
        ->set('experience', '2-5y')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->prefix->value)->toEqual('นาง');
    expect($user->first_name)->toEqual('สมศรี');
    expect($user->last_name)->toEqual('มีนา');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create([
        'position_id' => Position::factory()->create()->id,
        'affiliation_id' => Affiliation::factory()->create()->id,
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('prefix', $user->prefix->value)
        ->set('first_name', $user->first_name)
        ->set('last_name', $user->last_name)
        ->set('email', $user->email)
        ->set('position_id', $user->position_id)
        ->set('affiliation_id', $user->affiliation_id)
        ->set('experience', $user->experience->value)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
