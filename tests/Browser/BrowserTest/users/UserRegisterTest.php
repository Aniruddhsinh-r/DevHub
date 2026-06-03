<?php

use Illuminate\Support\Facades\Auth;

test('Register a user.', function () {
    visit('/register')
        ->fill('name', 'khabib')
        ->fill('email', 'khabib@gmail.com')
        ->fill('password', 'khabib')
        ->press('Register')
        ->assertPathIs('/');
        // ->assertPathIs('/home');         // exact path match ✓
        // ->assertPathBeginsWith('/home'); // path starts with
        // ->assertPathContains('home');    // path contains
        // ->assertPathEndsWith('/home')   // path ends with
        // ->assertPathIsNot('/register')  // path is NOT this

    expect(Auth::user())->toMatchArray([
        'name' => 'khabib',
    ]);
});
