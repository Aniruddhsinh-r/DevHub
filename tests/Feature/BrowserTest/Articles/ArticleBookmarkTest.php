<?php

use App\Models\Article;
require_once __DIR__.'/../Helpers/userLogin.php';
require_once __DIR__.'/../Helpers/adminLogin.php';

test('User can bookmark article', function () {
    userLogin();

    $article = Article::find(29);

    visit(route('articles.show', $article->id))
    ->click('[data-test="bookmark-button"]');

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => 22,
        'article_id' => $article->id,
    ]);
});

test('User can remove articles from bookmark', function () {
    userLogin();

    $article = Article::find(29);

    visit(route('articles.show', $article->id))
    ->click('[data-test="bookmark-button"]');

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => 22,
        'article_id' => $article->id,
    ]);
});

test('admin cant bookmark articles', function () {
    adminLogin();

    $article = Article::find(29);

    visit(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => 1,
        'article_id' => $article->id,
    ]);
});

test('guest cant Bookmark article', function () {
    $article = Article::find(29);

    visit(route('articles.show', $article->id))
    ->assertRoute('login');
});
