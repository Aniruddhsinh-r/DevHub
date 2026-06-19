<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

require_once __DIR__.'/../../Helpers/adminLogin.php';
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('it update user detail', function () {
    userLogin();

    visit(route('profile.edit'))
    ->fill('name', 'Rathod Ani')
    ->fill('bio', 'hi there this is my updated by using test case update profile testig.')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', 'rathod1290')
    ->fill('password_confirmation', 'rathod1290')
    ->press('@update_profile')
    ->assertSee('your profile is successfully updated.');

    $this->assertDatabaseHas('users', [
        'name' => 'Rathod Ani',
        'bio' => 'hi there this is my updated by using test case update profile testig.',
        'email' => 'adanirudda@gmail.com'
    ]);
});

test('guest cant see author profile', function() {
    $user = User::factory()->create();

    visit(route('profile.show',$user))
    ->assertRoute('login');
});

test('Admin cant access follow button profile page', function () {
    adminLogin();

    $user = User::factory()->create();

    visit(route('profile.show',$user))
    ->assertSee(403);
});
