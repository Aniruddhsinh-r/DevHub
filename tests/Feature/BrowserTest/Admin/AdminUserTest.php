<?php

use App\Models\User;

test('Admin fetch user details', function () {
    User::factory()->create([
        'name' => 'Loggy',
        'role' => 'author',
    ]);

    User::factory()->create([
        'name' => 'Siman',
        'role' => 'author',
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn')
    ->assertRoute('admin.dashboard');

    visit('/admin/users')
    ->assertSee('Loggy')
    ->assertSee('Siman');
});

test('search user', function () {
    User::factory()->create([
        'name' => 'ishigory',
        'role' => 'author',
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn');

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

// test('admin delete user',function () {
//     $user = User::where('name',)
// });
