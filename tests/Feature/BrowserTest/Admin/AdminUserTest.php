<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
require_once __DIR__.'/../../Helpers/adminLogin.php';
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('Admin fetch user details', function () {
    $user = userLogin();
    adminLogin();

    visit('/admin/users')
    ->assertSee($user->name);
});

test('admin search and delete user', function () {
    $user = userLogin();
    adminLogin();

    visit('/admin/users?search='.$user->name)
    ->assertSee($user->name)
    ->press('Remove');

    $this->assertDatabaseMissing('articles', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('comments', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('views', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id,]);
});

test('guest cant access user detail page', function () {
    visit('/admin/users')
    ->assertRoute('login');
});

test('Author cant access or search on user detail page', function () {
    $user = User::factory()->create();
    userLogin();

    visit('/admin/users?search='.$user->name)
    ->assertSee('403')
    ->assertSee('Forbidden');
});
