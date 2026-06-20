<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use Illuminate\Http\UploadedFile;
require_once __DIR__.'/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('Profile update test', function () {
    $user = userLogin();
    $newAvatar =  UploadedFile::fake()->image('avatar.jpg');

    Livewire::test('livewirecomponent.profile.edit-profile')
        ->set('name', 'Aniruddhsinh Rathod')
        ->set('bio', 'hi i am aniruddhsinh and i update my profile using testcase.')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('email', $user->email)
        ->set('avatar', $newAvatar)
        ->call('update');

    $this->assertDatabaseHas('users', [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
    ]);
});

test('Profile following functionality test', function () {
    $user = userLogin();
    $followed = User::factory()->create();

    Livewire::test('livewirecomponent.profile.profile',['user' => $followed])
    ->call('toggleFollow')
    ->assertDispatched('live-notification', message: 'Follow successfully.');

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});
