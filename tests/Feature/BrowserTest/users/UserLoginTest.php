<?php

use App\Models\User;
require_once __DIR__ . '/../Helpers/UserLogin.php';

test('it login a user', function () {
    visit('/login')
        ->fill('email', 'adanirudda@gmail.com')
        ->fill('password', '1290')
        ->press('@login-btn')
        // ->assertSee('you login successfully.');
        ->assertRoute('home');
});

test('it logout a user.', function () {
    userLogin();

    visit('/home')
    ->click('[data-test="Authbutton"]')
    ->click('[data-test="logout"]')
    ->assertRoute('home');
});
