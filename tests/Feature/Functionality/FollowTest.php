<?php

use App\Models\User;
use Livewire\Livewire;
use App\Enums\UserRole;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('user can follow but not twice and also unfollow', function () {
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

    Livewire::test('livewirecomponent.profile.profile',['user' => $followed])
        ->call('toggleFollow')
        ->assertDispatched('live-notification', message: 'Unfollow successfully.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});

test('admin cant access follow function page', function () {
    Role::create(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    $admin = AdminLogin();

    $this->get(route('profile.show', $user))
        ->assertStatus(403);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('admin cant follow Authors', function () {
    Role::create(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    $admin = AdminLogin();

    Livewire::test('livewirecomponent.profile.profile',['user' => $user])
    ->call('toggleFollow')
    ->assertSessionHas('error', 'Only Author can Follow others.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('Guest cant follow users', function () {
    Role::create(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);


    Livewire::test('livewirecomponent.profile.profile',['user' => $user])
    ->call('toggleFollow')
    ->assertRedirect(route('/'))
    ->assertSessionHas('error', 'Only Author can Follow others.');

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
    ]);
});