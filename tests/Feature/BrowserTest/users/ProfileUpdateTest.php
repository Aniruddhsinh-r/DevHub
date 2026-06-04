<?php

use App\Models\User;
require_once __DIR__.'/../Helpers/adminLogin.php';
require_once __DIR__.'/../Helpers/userLogin.php';

test('it update user detail', function () {
    visit('/login')
    ->fill('email','adanirudda@gmail.com')
    ->fill('password','1290')
    ->press('@login-btn')
    ->assertSee('You are loged in sucessfully!')
    // ->assertRoute('home')
    ->click('profile')
    ->press('Edit Profile')
    // ->assertRoute('profile.edit', $user->id)
    ->fill('name', 'Rathod Aniruddhsinh')
    ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', '129090')
    ->fill('password_confirmation', '129090')
    ->click('@update_profile')
    // ->assertRoute('profile.index')
    ->assertSee('your profile is sucessfully updated.');
    // $user = User::find(2);

    // visit(route('profile.edit', $user->id))
    //     ->fill('name', 'ani rathod')
    //     ->fill('email', 'adanirudda@gmail.com')
    //     ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
    //     ->fill('password', '129090')
    //     ->fill('password_confirmation', '129090')
    //     ->press('@update_profile');
    //     // ->assertRoute('profile.index');
    //     // ->assertPathIs('/profile');
    //     // ->assertRedirect(route('profile.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'Rathod Aniruddhsinh',
    ]);
    // $this->assertAuthenticated();
});

test('guest cant see author profile', function() {
    $user = User::find(22);

    visit(route('userprofile',$user->id))
    ->assertRoute('login');
    ;
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
