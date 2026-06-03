<?php

use App\Models\User;

test('Profile update test', function () {
    $user = User::find(22);
    $this->actingAs($user)->put(route('profile.update',$user->id), [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
        'password' => '1290',
        'password_confirmation' => '1290',
        'email' => $user->email,
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Aniruddhsinh Rathod',
        'bio' => 'hi i am aniruddhsinh and i update my profile using testcase.',
    ]);
});

test('Profile following functionality test', function () {
    $user = User::find(22);
    $followed = User::find(12);

    $this->actingAs($user)->post(route('user.follow', $user->id));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});
