<?php

use App\Models\Article;
use App\Models\User;

test('Guest cant post comment', function () {
    $article = Article::find(24);

    $response = $this->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'body' => 'guest try to comment on articles',
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'body' => 'guest try to comment on articles',
    ]);
});

test('admin cant post comment', function () {
    $article = Article::find(24);
    $user = User::find(1);

    $response = $this->actingAs($user)->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('comments', [
        'user_id' => $user->id,
    ]);
});

test('user can post comment', function () {
    $article = Article::find(24);
    $user = User::find(6);

    $response = $this->actingAs($user)->from(route('articles.show', $article->id))->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $response->assertRedirect(route('articles.show',$article->id));

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);
});
