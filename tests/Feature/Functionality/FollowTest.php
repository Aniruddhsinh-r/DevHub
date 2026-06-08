<?php

use App\Models\User;

test('user can follow but not twice and also unfollow', function () {
    $user = User::find(21);
    $followed = User::find(2);

    $this->actingAs($user)->post(route('user.follow', $followed->id), [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->actingAs($user)->post(route('user.follow', $followed->id), [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});

test('admin cant follow other author', function () {
    $user = User::find(21);
    $admin = User::find(1);

    $response = $this->actingAs($admin)->post(route('user.follow', $admin->id), [
        'follower_id' => $user->id,
        'followed_id' => $admin->id,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('Guest cant follow users', function () {
    $user = User::find(5);

    $response = $this->post(route('user.follow',$user), [
        'follower_id' => $user->id,
    ]);

    $response->assertStatus(302);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
    ]);
});
