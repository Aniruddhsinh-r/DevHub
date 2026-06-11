<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

require_once __DIR__.'/../../Helpers/adminLogin.php';
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('it update user detail', function () {
    userLogin();

    visit('/profile')
    ->press('Edit Profile')
    ->fill('name', 'Rathod Ani')
    ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', 'rathod1290')
    ->fill('password_confirmation', 'rathod1290')
    ->press('@update_profile');

    $this->assertDatabaseHas('users', [
        'name' => 'Rathod Ani',
    ]);
});

test('guest cant see author profile', function() {
    $user = User::factory()->create();

    visit(route('profile.show',$user))
    ->assertRoute('login');
});

test('Admin cant follow Author', function () {
    adminLogin();

    $user = User::factory()->create();

    visit(route('user.follow',$user));

    $this->assertDatabaseMissing('follows', [
        'follower_id' => auth()->id(),
        'followed_id' => $user->id
    ]);
});
