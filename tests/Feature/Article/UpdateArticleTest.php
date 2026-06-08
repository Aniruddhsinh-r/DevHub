<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can update his article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create([
        'user_id' => $user->id
    ]);

    $updateResponse = $this->actingAs($user)->patch(route('articles.update', $article->id), [
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
        'body' => $article->body,
        'category_id' => $article->category_id,
        'status' => $article->status,
    ]);

    $updateResponse->assertRedirect(route('publishedarticle'));

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
    ]);
});

test('user cannot update others article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    $updateResponse = $this->actingAs($user)->patch(route('articles.update', $article->id), [
        'title' => 'try to update unauthorized article.',
        'excerpt' => 'try to update unauthorized article using test, and perform update.',
        'body' => $article->body,
        'category_id' => $article->category_id,
        'status' => $article->status,
    ]);

    $updateResponse->assertStatus(403);

    $this->assertDatabaseMissing('articles', [
        'title' => 'try to update unauthorized article.',
        'excerpt' => 'try to update unauthorized article using test, and perform update.',
    ]);
});
