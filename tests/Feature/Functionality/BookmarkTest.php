<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin cant bookmark article', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $article = Article::factory()->create();

    $response = $this->actingAs($admin)->post(route('articles.bookmark',$article->id));

    $response->assertStatus(403);
    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});

test('guest cant bookmark article', function () {
    $article = Article::factory()->create();

    $this->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

test('user can bookmark article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user can bookmark but not twice', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));
    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user cant bookmark draft article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['status'=>'draft']);

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
