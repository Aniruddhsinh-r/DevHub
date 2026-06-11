<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('Admin fetch user details', function () {
    User::factory()->create([
        'name' => 'Loggy',
        'role' => 'author',
    ]);

    User::factory()->create([
        'name' => 'Siman',
        'role' => 'author',
    ]);

    adminLogin();

    visit('/admin/users')
    ->assertSee('Loggy')
    ->assertSee('Siman');
});

test('admin search and delete user', function () {
    $user = User::factory()->create([
        'name' => 'ishigory',
        'role' => 'author',
    ]);

    adminLogin();

    visit('/admin/users?search=ishigory')
    ->assertSee('ishigory')
    ->press('Remove');

    $this->assertDatabaseMissing('articles', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('comments', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('views', ['user_id' => $user->id,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $user->id,]);
});
