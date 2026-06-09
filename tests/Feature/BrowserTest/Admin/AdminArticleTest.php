<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('Admin fetch user details', function () {
    $user_id = User::

    $article = Article::factory()->create([
        'title' => 'example Article',
        'user_id' => $user_id,
    ]);

    adminLogin();

    visit('/admin/articles?search=example Article')
    ->assertSee('example Article')
    ->click('example Article')
    ->assertPathIs('/admin/articles/' . $article->id);
});
