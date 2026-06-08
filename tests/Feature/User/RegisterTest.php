<?php

test('user registration test', function () {
    $email = 'khabib'.time().'@gmail.com';
    $response = $this->post(route('register.store'), [
        'name' => 'khabib',
        'email' => $email,
        'password' => 'khabib',
    ]);

    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'name' => 'khabib',
        'email' => $email,
        'role' => 'author',
    ]);
});
