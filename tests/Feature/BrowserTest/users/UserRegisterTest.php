<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('Register a user.', function () {
    beforeEach(function () {
        Role::firstOrCreate([
            'name' => 'author',
            'guard_name' => 'web'
        ]);
    });
    $email = 'roman'.time().'@gmail.com';

    visit(route('register.create'))
        ->fill('name', 'Romanreigns+')
        ->fill('email', $email)
        ->fill('password', 'Roman123')
        ->click('Register')
        ->assertRoute('home');

    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});
