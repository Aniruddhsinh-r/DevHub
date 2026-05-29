<?php

use Illuminate\Support\Facades\Auth;

test('Register a user.', function () {
    visit('/register')
        ->fill('name', 'rathodkcp')
        ->fill('email', 'aniruddhsinh12@gmail.com')
        ->fill('password', '1290')
        ->press('Register')
        ->assertPathIs('home');

    $this->assertAuthenticated();

    expect(Auth::user())->toMatchArray([
        'name' => 'rathodk',
    ]);

    $response->assertStatus(200);
});
