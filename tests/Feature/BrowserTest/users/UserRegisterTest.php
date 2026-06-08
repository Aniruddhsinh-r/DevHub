<?php

use Illuminate\Support\Facades\Auth;

test('Register a user.', function () {
    $email = 'roman'.time().'@gmail.com';

    visit(route('register.create'))
        ->fill('name', 'Romanreigns')
        ->fill('email', $email)
        ->fill('password', 'Roman123')
        ->click('Register')
        ->assertRoute('home');
        // ->assertPathIs('/home');         // exact path match ✓
        // ->assertPathBeginsWith('/home'); // path starts with
        // ->assertPathContains('home');    // path contains
        // ->assertPathEndsWith('/home')   // path ends with
        // ->assertPathIsNot('/register')  // path is NOT this


    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});
