<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../Helpers/userLogin.php';
require_once __DIR__.'/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('User can like article', function () {
    userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article->id))
    ->press('@like-button');

    $this->assertDatabaseHas('likes', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('User can unlike article', function () {
    userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article->id))
    ->press('@like-button');
    visit(route('articles.show', $article->id))
    ->press('@like-button');

    $this->assertDatabaseMissing('likes', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('admin cant like articles', function () {
    adminLogin();

    $article = Article::factory()->create();

    visit(route('articles.like',$article->id));

    $this->assertDatabaseMissing('likes', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('guest cant like article', function () {
    $article = Article::factory()->create();
    
    visit(route('articles.show', $article->id))
    ->assertRoute('login');
});
