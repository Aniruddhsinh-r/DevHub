<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../Helpers/adminLogin.php';

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
    User::factory()->create([
        'name' => 'ishigory',
        'role' => 'author',
    ]);

    adminLogin();

    $userId = User::where('name','ishigory')->value('id');

    visit('/admin/users?search=ishigory')
    ->assertSee('ishigory')
    ->press('Remove');

    $this->assertDatabaseMissing('articles', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('comments', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('views', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $userId,]);
});
