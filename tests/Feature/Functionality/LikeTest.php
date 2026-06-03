<?php

use App\Models\Article;
use App\Models\User;

test('user can like but not twice', function () {
    $article = Article::find(4);
    $user = User::find(12);

    $this->actingAs($user)->post(route('articles.like',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('likes', [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->post(route('articles.like',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('admin cant like', function () {
    $article = Article::find(21);
    $admin = User::find(1);

    $response = $this->actingAs($admin)->post(route('articles.like',$article->id), [
        'article_id' => $article->id,
        'user_id' => $admin->id,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
    ]);
});

test('user cant like draft article', function () {
    $user = User::find(22);
    $article = Article::find(2);

    $this->actingAs($user)->post(route('articles.like',$article->id));

    $this->assertDatabaseMissing('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
