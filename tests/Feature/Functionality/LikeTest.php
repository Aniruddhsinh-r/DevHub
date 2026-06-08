<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can like but not twice', function () {
    $article = Article::factory()->create();
    $user = User::factory()->create();

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
    $article = Article::factory()->create();
    $admin = User::factory()->create(['role'=>'admin']);

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
    $user = User::factory()->create();
    $article = Article::factory()->create(['status' => 'draft']);

    $this->actingAs($user)->post(route('articles.like',$article->id));

    $this->assertDatabaseMissing('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
