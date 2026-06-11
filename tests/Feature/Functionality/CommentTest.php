<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('Guest cant post comment', function () {
    $article = Article::factory()->create();

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
    $article = Article::factory()->create();
    $admin =  adminLogin();

    $response = $this->actingAs($admin)->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'user_id' => $admin->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('comments', [
        'user_id' => $admin->id,
    ]);
});

test('user can post comment', function () {
    $article = Article::factory()->create();
    $user = userLogin();

    $response = $this->actingAs($user)->from(route('articles.show', $article))->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $response->assertRedirect(route('articles.show',$article));

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);
});
