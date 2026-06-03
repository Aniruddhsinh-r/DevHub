<?php

use App\Models\User;

test('check article validation test', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->post(route('articles.store'), [
        'title' => '',
        'category_id' => '344',
        'excerpt' => 'qerr',
        'body' => '13333213313',
        'status' => 'drafts',

    ]);

    $response->dump();

    $response->assertSessionHasErrors('title');
    $response->assertSessionHasErrors('category_id');
    $response->assertSessionHasErrors('excerpt');
    $response->assertSessionHasErrors('body');
    $response->assertSessionHasErrors('status');
});

test('check register validation test', function () {
    $response = $this->post(route('register.create'), [
        'name' => 'Visu',
        'email' => 'vishugmail.com',
        'password' => '12',
        'bio' => 34
    ]);

    $response->dump();

    $response->assertSessionHasErrors('name');
    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors('password');
    $response->assertSessionHasErrors('bio');
});

test('check login validation test', function () {
    $response = $this->post(route('login'), [
        'email' => 'adaniruddhagmail.com',
        'password' => '12',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors('password');
});

test('check credentials validation test', function () {
    $response = $this->post(route('login'), [
        'email' => 'adaniruddha@gmail.com',
        'password' => '1290',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'The provided credentials do not match our records.');
});

test('check schedule article validation test require minutes', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->post(route('articles.store'), [
        'title' => 'expose your self',
        'category_id' => '3',
        'excerpt' => 'article for know who are you.',
        'body' => 'hi there good that i attract you anyway lets talk that how to know ourself...',
        'status' => 'scheduled',
        'scheduled_minutes' => ''
    ]);

    $response->dump();

    $response->assertSessionHasErrors('scheduled_minutes');
});

test('check profile update validation test', function () {
    $user = User::find(22);

    $response = $this->actingAs($user)->put(route('profile.update',$user), [
        'name' => '',
        'bio' => '344',
        'email' => 'qerr',
        'password' => '13333213313',
        'password_confirmation' => 'drafts',
    ]);

    $response->dump();

    $response->assertSessionHasErrors('name');
    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors('password');
});

test('User cant see author profile', function () {
    $user = User::find(22);
    $admin = User::find(1);

    $response = $this->actingAs($user)->get(route('userprofile',$admin));
    $response->assertStatus(302);

    $response->assertSessionHas('error', 'This author does not exist.');
});

test('User cant see those auther who dose not exist in db', function () {
    $user = User::find(22);
    $admin = 189;

    $response = $this->actingAs($user)->get(route('userprofile',$admin));
    $response->assertStatus(404);
});

test('Admin cant update his profile', function () {
    $admin = User::find(1);

    $response = $this->actingAs($admin)->put(route('profile.update',$admin),[
        'name' => 'Admin Name change',
        'email' => 'admin@example.com',
    ]);
    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'name' => 'Harshrajsinh',
        'email' => 'harshrajsinh@gmail.com',
        'role' => 'admin',
    ]);
});
