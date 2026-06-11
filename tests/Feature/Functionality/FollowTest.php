<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('user can follow but not twice and also unfollow', function () {
    $user = userLogin();
    $followed = User::factory()->create();

    $this->actingAs($user)->post(route('user.follow', $followed), [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->actingAs($user)->post(route('user.follow', $followed), [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});

test('admin cant follow other author', function () {
    $user = userLogin();
    $admin = adminLogin();

    $response = $this->actingAs($admin)->post(route('user.follow', $admin), [
        'follower_id' => $user->id,
        'followed_id' => $admin->id,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('Guest cant follow users', function () {
    $user = userLogin();

    $response = $this->post(route('user.follow',$user), [
        'follower_id' => $user->id,
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
    ]);
});
