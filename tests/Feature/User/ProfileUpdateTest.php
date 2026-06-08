<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Profile update test', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('profile.update',$user->id), [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
        'password' => 'rathod1290',
        'password_confirmation' => 'rathod1290',
        'email' => $user->email,
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
    ]);
});

test('Profile following functionality test', function () {
    $user = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($user)->post(route('user.follow', $followed->id));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});
