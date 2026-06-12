<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';
uses(RefreshDatabase::class);

test('Profile update test', function () {
    $user = userLogin();
    $this->actingAs($user)->put(route('profile.update',$user), [
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
    $user = userLogin();
    $followed = User::factory()->create();

    $this->actingAs($user)->post(route('user.follow', $followed));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});
