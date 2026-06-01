<?php

use App\Models\User;
require_once __DIR__.'/../../Helpers/userLogin.php';

test('it login a user', function () {
    userLogin();

    visit(route('profile.edit', 22))
        ->fill('name', 'ani rathod')
        ->fill('email', 'adanirudda@gmail.com')
        ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
        ->fill('password', '129090')
        ->fill('password_confirmation', '129090')
        ->press('Update')
        ->assertRoute('profile.index');
        // ->assertPathIs('/profile');
        // ->assertRedirect(route('profile.index'));

    $this->assertDatabaseHas('users', [
        'id' => 22,
        'name' => 'ani rathod',
    ]);
    // $this->assertAuthenticated();
});
