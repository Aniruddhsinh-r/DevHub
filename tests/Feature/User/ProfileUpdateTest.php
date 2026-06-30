<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\UploadedFile;
require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('Profile update test', function () {
    $user = UserLogin();
    $newAvatar =  UploadedFile::fake()->image('avatar.jpg');

    Livewire::test('livewirecomponent.profile.edit-profile')
        ->set('name', 'Aniruddhsinh Rathod')
        ->set('bio', 'hi i am aniruddhsinh and i update my profile using testcase.')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('email', 'adniruddha@gmail.com')
        ->set('avatar', $newAvatar)
        ->call('update');

    $this->assertDatabaseHas('users', [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
    ]);
});

test('Profile following functionality test', function () {
    $user = UserLogin();
    $followed = User::factory()->create();
    $followed->assignRole(UserRole::AUTHOR);

    Livewire::test('livewirecomponent.profile.profile',['user' => $followed])
    ->call('toggleFollow')
    ->assertDispatched('live-notification', message: 'Follow successfully.');

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});