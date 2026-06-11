<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// require_once __DIR__.'/../../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('User Login and logout test', function () {
    userLogin();

    $this->post(route('login.store'), [
        'email' => 'adanirudda@gmail.com',
        'password' => 'rathod1290'
    ]);

    $this->assertAuthenticated();

    $response = $this->post(route('logout'));
    $this->assertGuest();

    $response->assertRedirect(route('home'));
});

test('Logged in user cannot visit login form', function () {
    $user = userLogin();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('home'));
});

test('Logged in user cannot visit register form', function () {
    $user = userLogin();

    $response = $this->actingAs($user)->get(route('register.create'));

    $response->assertRedirect(route('home'));
});

test('Login user cant login again', function () {
    $user = userLogin();

    $response = $this->actingAs($user)->post(route('login.store'), [
        'email' => 'adanirudda@gmail.com',
        'password' => '1290'
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});
