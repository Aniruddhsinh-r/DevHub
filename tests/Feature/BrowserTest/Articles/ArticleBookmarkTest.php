<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';
require_once __DIR__.'/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('User can bookmark article', function () {
    userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->press('@bookmark-button');

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('User can remove articles from bookmark', function () {
    userLogin();

    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->press('@bookmark-button');
    visit(route('articles.show', $article))
    ->press('@bookmark-button');

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('admin cant bookmark articles', function () {
    adminLogin();

    $article = Article::factory()->create();

    visit(route('articles.bookmark',$article));

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
    ]);
});

test('guest cant Bookmark article', function () {
    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->assertRoute('login');
});
