<?php

use App\Models\User;
require_once __DIR__.'/../Helpers/adminLogin.php';
require_once __DIR__.'/../Helpers/userLogin.php';

test('it update user detail', function () {
    userLogin();

    visit('/profile')
    ->press('Edit Profile')
    // ->assertRoute('profile.edit', $user->id)
    ->fill('name', 'Rathod Aniruddhsinh')
    ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', '1290')
    ->fill('password_confirmation', '1290')
    ->press('@update_profile');

    $this->assertDatabaseHas('users', [
        'name' => 'Rathod Aniruddhsinh',
    ]);
    // $this->assertAuthenticated();
});

test('guest cant see author profile', function() {
    $user = User::find(22);

    visit(route('userprofile',$user->id))
    ->assertRoute('login');
});

test('Admin cant follow Author', function () {
    adminLogin();

    $admin = User::find(1);
    $user = User::find(22);

    visit(route('user.follow',$user->id));

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
        'followed_id' => $user->id
    ]);
});
