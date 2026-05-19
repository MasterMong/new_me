<?php

test('public directory page can be accessed', function () {
    $this->get(route('directory'))
        ->assertOk()
        ->assertSee('ทำเนียบนักติดตาม');
});

test('public contact page can be accessed', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('ติดต่อสอบถาม');
});

test('contact form validates required fields', function () {
    \Livewire\Livewire::test(\App\Livewire\Public\Contact::class)
        ->call('sendMessage')
        ->assertHasErrors(['name', 'email', 'subject', 'message']);
});
