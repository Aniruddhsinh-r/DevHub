<?php

use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate([
        'name' => 'author',
        'guard_name' => 'web'
    ]);
});
test('user registration test', function () {
    $email = 'khabib'.time().'@gmail.com';
    $response = $this->post(route('register.store'), [
        'name' => 'khabibji',
        'email' => $email,
        'password' => 'khabibji',
    ]);

    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'name' => 'khabibji',
        'email' => $email,
    ]);
});
