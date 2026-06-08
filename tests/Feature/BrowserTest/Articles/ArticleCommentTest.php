<?php

use App\Models\Article;
require_once __DIR__ . '/../Helpers/UserLogin.php';

test('Author comment on article', function () {
    UserLogin();

    $article = Article::find(6);
    visit(route('articles.show', $article->id))
    ->fill('body',"hello this comment created by browser comment testing.")
    ->press('@PostComment');

    $this->assertDatabaseHas('comments', [
        'user_id' => 22,
        'article_id' => $article->id,
        'body' => 'hello this comment created by browser comment testing.',
    ]);
});
