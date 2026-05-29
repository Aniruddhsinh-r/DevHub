<?php

use App\Models\User;

test('it login a user', function () {
    visit('/login')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', '1290')
    ->press('@login-btn');

    visit('/profile/22/edit')
        ->fill('name', 'ani rathod')
        ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
        ->fill('password', '129090')
        ->fill('password_confirmation', '129090')
        ->press('Update')
        ->assertRoute('profile.index');
        // ->assertPathIs('profile.index');

    // $this->assertAuthenticated();
});
