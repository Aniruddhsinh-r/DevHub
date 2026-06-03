<?php

use App\Models\User;

test('User Login and logout test', function () {
    $this->post(route('login.store'), [
        'email' => 'adanirudda@gmail.com',
        'password' => '1290'
    ]);

    $this->assertAuthenticated();

    $response = $this->post(route('logout'));
    $this->assertGuest();

    $response->assertRedirect(route('home'));
    // $this->assertDatabaseMissing('users', ['id' => $response->id]);
});

test('Logged in user cannot visit login form', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('home'));
});

test('Logged in user cannot visit register form', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->get(route('register.create'));

    $response->assertRedirect(route('home'));
});

test('Login user cant login again', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->post(route('login.store'), [
        'email' => 'adanirudda@gmail.com',
        'password' => '1290'
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);
});
