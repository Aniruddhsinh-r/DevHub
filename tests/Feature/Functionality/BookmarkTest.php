<?php

use App\Models\Article;
use App\Models\User;

test('admin cant bookmark article', function () {
    $admin = User::find(1);
    $article = Article::find(3);

    $response = $this->actingAs($admin)->post(route('articles.bookmark',$article->id));

    $response->assertStatus(403);
    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});

test('guest cant bookmark article', function () {
    $article = Article::find(3);

    $response = $this->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

test('user can bookmark article', function () {
    $user = User::find(22);
    $article = Article::find(3);

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user can bookmark but not twice', function () {
    $user = User::find(22);
    $article = Article::find(3);

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user cant bookmark draft article', function () {
    $user = User::find(22);
    $article = Article::find(2);

    $response = $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
