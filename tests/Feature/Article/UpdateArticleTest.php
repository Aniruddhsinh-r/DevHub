<?php

use App\Models\Article;
use App\Models\User;

test('user can update his article', function () {
    $user = User::find(21);
    $article = Article::find(20);
    $updateResponse = $this->actingAs($user)->patch(route('articles.update', $article->id), [
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
        'body' => $article->body,
        'category_id' => $article->category_id,
        'status' => $article->status,
        'published_at' => $article->published_at,
    ]);

    $updateResponse->assertRedirect(route('publishedarticle'));

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
    ]);
});

test('user cannot update others article', function () {
    $user = User::find(5);
    $article = Article::find(7);

    $updateResponse = $this->actingAs($user)->patch(route('articles.update', $article->id), [
        'title' => 'try to update unauthorized article.',
        'excerpt' => 'try to update unauthorized article using test, and perform update.',
        'body' => $article->body,
        'category_id' => $article->category_id,
        'status' => $article->status,
        'published_at' => $article->published_at,
    ]);

    $updateResponse->assertStatus(403);

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Eum voluptatem quia omnis eum beatae facere sed veritatis nihil temporibus adipisci optio qui eius quia et iure animi.',
        'excerpt' => 'Ut veniam eum veniam facilis quos corporis.',
    ]);
});
