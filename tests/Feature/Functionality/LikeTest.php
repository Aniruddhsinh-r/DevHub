<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/adminLogin.php';
require_once __DIR__ . '/../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('user can like but not twice', function () {
    $article = Article::factory()->create();
    $user = userLogin();

    $this->actingAs($user)->post(route('articles.like',$article), [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('likes', [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->post(route('articles.like',$article), [
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
    $admin = adminLogin();

    $response = $this->actingAs($admin)->post(route('articles.like',$article), [
        'article_id' => $article->id,
        'user_id' => $admin->id,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
    ]);
});

test('user cant like draft article', function () {
    $user = userLogin();
    $article = Article::factory()->create(['status' => 'draft']);

    $this->actingAs($user)->post(route('articles.like',$article));

    $this->assertDatabaseMissing('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
