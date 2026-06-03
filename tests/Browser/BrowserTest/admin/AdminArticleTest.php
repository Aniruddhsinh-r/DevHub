<?php

use App\Models\Article;
use App\Models\User;

test('Admin fetch user details', function () {
    $user_id = User::where('email','adanirudda@gmail.com')->value('id');

    $article = Article::factory()->create([
        'title' => 'example Article',
        'user_id' => $user_id,
        'status' => 'published'
    ]);

    visit('/login')
    ->fill('email', 'harshrajsinh@gmail.com')
    ->fill('password', 'IAmHarsh')
    ->press('@login-btn')
    ->assertRoute('admin.dashboard');

    visit('/admin/articles?search=example Article')
    ->assertSee('example Article')
    ->click('example Article')
    ->assertPathIs('/admin/articles/' . $article->id);
});
