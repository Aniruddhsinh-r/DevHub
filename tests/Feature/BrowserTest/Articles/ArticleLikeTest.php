<?php

use App\Models\Article;
require_once __DIR__.'/../Helpers/userLogin.php';
require_once __DIR__.'/../Helpers/adminLogin.php';

// test('User can like article', function () {
//     userLogin();

//     $article = Article::find(29);

//     visit(route('articles.show', $article->id))
//     ->click('[data-test="like-button"]');

//     $this->assertDatabaseHas('likes', [
//         'user_id' => 22,
//         'article_id' => $article->id,
//     ]);
// });

// test('User can unlike article', function () {
//     userLogin();

//     $article = Article::find(29);

//     visit(route('articles.show', $article->id))
//     ->click('[data-test="like-button"]');

//     $this->assertDatabaseMissing('likes', [
//         'user_id' => 22,
//         'article_id' => $article->id,
//     ]);
// });

// test('admin cant like articles', function () {
//     adminLogin();

//     $article = Article::find(29);

//     visit(route('articles.like',$article->id));

//     $this->assertDatabaseMissing('likes', [
//         'user_id' => 1,
//         'article_id' => $article->id,
//     ]);
// });

test('guest cant like article', function () {
    $article = Article::find(29);

    visit(route('articles.show', $article->id))
    ->assertRoute('login');
});
