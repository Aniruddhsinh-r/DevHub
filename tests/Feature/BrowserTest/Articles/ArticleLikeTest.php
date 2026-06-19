<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';
require_once __DIR__.'/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('User can like article', function () {
    $user = userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->click('@like-button')
    ->assertSee('article like');

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('User can unlike article', function () {
    $user = userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->press('@like-button')
    ->assertSee('article like');

    visit(route('articles.show', $article))
    ->press('@like-button')
    ->assertSee('article unlike');

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('admin cant access article like page', function () {
    $admin = adminLogin();

    $article = Article::factory()->create();

    visit(route('articles.show',$article));

    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
        'article_id' => $article->id,
    ]);
});

test('guest cant like article', function () {
    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->assertRoute('login');
});