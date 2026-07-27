<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('User Login and logout test', function () {
    UserLogin();

    $this->post(route('login'), [
        'email' => 'adanirudda@gmail.com',
        'password' => 'rathod1290'
    ]);

    $this->assertAuthenticated();

    $response = $this->post(route('logout'));
    $this->assertGuest();

    $response->assertRedirect(route('filament.app.pages.home'));
});

test('Logged in user cannot visit login form', function () {
    $user = UserLogin();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('filament.app.pages.home'));
});

test('Logged in user cannot visit register form', function () {
    $user = UserLogin();

    $response = $this->actingAs($user)->get(route('register.create'));

    $response->assertRedirect(route('filament.app.pages.home'));
});

test('Login user cant login again', function () {
    $user = UserLogin();

    $response = $this->actingAs($user)->get(route('login'), [
        'email' => 'adanirudda@gmail.com',
        'password' => '1290'
    ]);

    $response->assertRedirect(route('filament.app.pages.home'));

    $this->assertAuthenticatedAs($user);
});
