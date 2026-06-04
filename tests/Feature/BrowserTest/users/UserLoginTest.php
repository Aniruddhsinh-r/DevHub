<?php

use App\Models\User;

test('it login a user', function () {
    visit('/login')
        ->fill('email', 'adanirudda@gmail.com')
        ->fill('password', '1290')
        ->press('@login-btn')
        // ->assertSee('you login successfully.');
        ->assertRoute('home');
        // ->assertPathIs(route('home', [], false));

    // $this->assertAuthenticated();
});

test('it logout a user.', function () {
    $user = User::find(4);

    $this->actingAs($user)->visit('home')
    ->click('@Logout')
    ->assertRoute('home');
});
