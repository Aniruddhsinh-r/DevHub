<?php

use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../Helpers/userLogin.php';
require_once __DIR__.'/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('check article validation test', function () {
    userLogin();
    Livewire::test('livewirecomponent.article.create-article')
        ->set('title','')
        ->set('category_id','344')
        ->set('excerpt','article created by kishan that gonna delete for purpose. wetw rtt wtwtwrgr  wrgg g rt t ret r t rt er ter e d gd g g')
        ->set('body','')
        ->set('status','drafts')
        ->call('store')
        ->assertHasErrors(['title' => 'required'])
        ->assertHasErrors(['category_id' => 'exists:categories,id'])
        ->assertHasErrors(['excerpt' => 'max'])
        ->assertHasErrors(['body' => 'required'])
        ->assertHasErrors(['status' => 'in']);
});

test('check register validation test', function () {
    Livewire::test('livewirecomponent.auth.register')
        ->set('name','Visu')
        ->set('email','vishagmail.com')
        ->set('password','12')
        ->set('bio',34)
        ->call('register')
        ->assertHasErrors(['name' => 'min'])
        ->assertHasErrors(['email' => 'email'])
        ->assertHasErrors(['password' => 'min'])
        ->assertHasErrors(['bio' => 'string']);
});

test('check login validation test', function () {
    Livewire::test('livewirecomponent.auth.login')
        ->set('email','adaniruddhagmail.com')
        ->set('password','12')
        ->call('login')
        ->assertHasErrors(['email' => 'email'])
        ->assertHasErrors(['password' => 'min']);
});

test('check credentials validation test', function () {
    Livewire::test('livewirecomponent.auth.login')
    ->set('email','adaniruddha@gmail.com')
    ->set('password','rathod1290')
    ->call('login')
    ->assertDispatched('live-notification', message: 'The provided credentials do not match our records.');
});

test('check schedule article validation test require minutes', function () {
    userLogin();

    Livewire::test('livewirecomponent.article.create-article')
        ->set('title','expose your self')
        ->set('category_id','3')
        ->set('excerpt','article for know who are you.')
        ->set('body','hi there good that i attract you anyway lets talk that how to know ourself...')
        ->set('status','scheduled')
        ->set('scheduled_minutes',0)
        ->call('store')
        ->assertHasErrors(['scheduled_minutes' => 'min']);
});

test('check profile update validation test', function () {
    $user = userLogin();

    $test = Livewire::test('livewirecomponent.profile.edit-profile')
        ->set('name', '')
        ->set('bio',  342432)
        ->set('password', '13333213313')
        ->set('password_confirmation', 'drafts')
        ->call('update');

    $test->assertHasErrors([
        'name' => 'required',
        'bio' => 'string',
        'password' => 'confirmed'
    ]);
});

test('Author cant see admin profile', function () {
    $admin = adminLogin();
    userLogin();

    $response = Livewire::test('livewirecomponent.profile.profile',['user' => $admin]);
    $response->assertStatus(302);
    $response->assertRedirect('/home');
    $response->assertSessionHas('error','This author does not exist.');
});

test('User cant see those auther who dose not exist in db', function () {
    userLogin();

    $this->get('/profile/4343223')->assertStatus(404);
});

test('Admin cant update his profile', function () {
    $admin = adminLogin();

    $response = $this->actingAs($admin)->get(route('profile.edit'),[
        'name' => 'Admin Name change',
        'email' => 'admin@example.com',
    ]);
    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'name' => $admin->name,
        'email' => $admin->email,
    ]);
});
