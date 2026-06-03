<?php

use App\Models\User;

test('Admin fetch user details', function () {
    User::factory()->create([
        'name' => 'Aniruddh',
        'email' => 'ani5@gmail.com',
        'role' => 'author',
    ]);

    User::factory()->create([
        'name' => 'Harsh',
        'email' => 'harsh5@gmail.com',
        'role' => 'author',
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn')
    ->assertRoute('admin.dashboard');

    visit('/admin/users')
    ->assertSee('Aniruddh')
    ->assertSee('ani5@gmail.com')
    ->assertSee('Harsh')
    ->assertSee('harsh5@gmail.com');
});

test('search user', function () {
    User::factory()->create([
        'name' => 'kishan',
        'email' => 'kishansinh1@gmail.com',
        'role' => 'author',
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh1@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn');

    $userId = User::where('email','kishansinh1@gmail.com')->value('id');

    visit('/admin/users?search=kishan')
    ->assertSee('kishan')
    ->assertSee('kishansinh1@gmail.com')
    ->press('Remove');

    $this->assertDatabaseMissing('articles', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('likes', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('comments', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('views', ['user_id' => $userId,]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $userId,]);
});
