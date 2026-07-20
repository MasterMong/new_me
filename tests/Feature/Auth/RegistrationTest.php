<?php

use App\Models\Affiliation;
use App\Models\Position;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $position = Position::factory()->create();
    $affiliation = Affiliation::factory()->create();

    $response = $this->post(route('register.store'), [
        'prefix' => 'นาย',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'phone' => '0812345678',
        'position_id' => $position->id,
        'affiliation_id' => $affiliation->id,
        'school_name' => 'Test School',
        'experience' => '<2y',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('registration requires position and affiliation', function () {
    $response = $this->post(route('register.store'), [
        'prefix' => 'นาย',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'experience' => '<2y',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['position_id', 'affiliation_id']);
    $this->assertGuest();
});
