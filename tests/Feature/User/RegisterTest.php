<?php

test('user registration test', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'khabib',
        'email' => 'khabib26@gmail.com',
        'password' => 'khabib',
    ]);

    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'name' => 'khabib',
        'email' => 'khabib26@gmail.com',
        'role' => 'author',
    ]);
});
