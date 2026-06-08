<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('Register a user.', function () {
    $email = 'roman'.time().'@gmail.com';

    visit(route('register.create'))
        ->fill('name', 'Romanreigns')
        ->fill('email', $email)
        ->fill('password', 'Roman123')
        ->click('Register')
        ->assertRoute('home');

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});
