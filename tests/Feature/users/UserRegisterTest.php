<?php

use Illuminate\Support\Facades\Auth;

test('Register a user.', function () {
    visit('/register')
        ->fill('Full name', 'rathodkcp')
        ->fill('Your email', 'aniruddhsinh24@gmail.com')
        ->fill('Password', '129000')
        ->press('Register')
        ->assertRoute('home');
        // ->assertPathIs('home');         // exact path match ✓
        // ->assertPathBeginsWith('/home'); // path starts with
        // ->assertPathContains('home');    // path contains
        // ->assertPathEndsWith('/home')   // path ends with
        // ->assertPathIsNot('/register')  // path is NOT this

    expect(Auth::user())->toMatchArray([
        'name' => 'rathodkcp',
    ]);

});
