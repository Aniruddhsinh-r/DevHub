<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('user can follow but not twice and also unfollow', function () {
    $user = userLogin();
    $followed = User::factory()->create();

    Livewire::test('livewirecomponent.profile.profile',['user' => $followed])
        ->call('toggleFollow')
        ->assertDispatched('live-notification', message: 'Follow successfully.');

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    Livewire::test('livewirecomponent.profile.profile',['user' => $followed])
        ->call('toggleFollow')
        ->assertDispatched('live-notification', message: 'Unfollow successfully.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});

test('admin cant access follow function page', function () {
    $user = userLogin();
    $admin = adminLogin();

    $this->get(route('profile.show', $user))
        ->assertStatus(403);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('admin cant follow Authors', function () {
    $user = userLogin();
    $admin = adminLogin();

    Livewire::test('livewirecomponent.profile.profile',['user' => $user])
    ->call('toggleFollow')
    ->assertRedirect(route('/'))
    ->assertSessionHas('error', 'Only Author can Follow others.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('Guest cant follow users', function () {
    $user = User::factory()->create();

    Livewire::test('livewirecomponent.profile.profile',['user' => $user])
    ->call('toggleFollow')
    ->assertRedirect(route('/'))
    ->assertSessionHas('error', 'Only Author can Follow others.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
    ]);
});