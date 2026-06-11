<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('admin cant bookmark article', function () {
    $admin = adminLogin();
    $article = Article::factory()->create();

    $response = $this->actingAs($admin)->post(route('articles.bookmark',$article));

    $response->assertStatus(403);
    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});

test('guest cant bookmark article', function () {
    $article = Article::factory()->create();

    $this->post(route('articles.bookmark',$article));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

test('user can bookmark article', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    $this->actingAs($user)->post(route('articles.bookmark',$article));

    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user can bookmark but not twice', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    $this->actingAs($user)->post(route('articles.bookmark',$article));
    $this->actingAs($user)->post(route('articles.bookmark',$article));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user cant bookmark draft article', function () {
    $user = userLogin();
    $article = Article::factory()->create(['status'=>'draft']);

    $this->actingAs($user)->post(route('articles.bookmark',$article));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
