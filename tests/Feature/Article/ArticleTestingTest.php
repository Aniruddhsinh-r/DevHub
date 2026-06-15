<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('user can comment like and bookmark article', function () {
    $user = userLogin();

    $article = Article::factory()->create();
    $response = $this->actingAs($user)->get(route('articles.show', $article));

    $this->actingAs($user)->post(route('post.comment',$article), [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $this->actingAs($user)->post(route('articles.bookmark',$article));

    $this->actingAs($user)->post(route('articles.like',$article));

    $this->assertDatabaseHas('views',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('comments',['article_id'=>$article->id ,
        'user_id'=>$user->id,
        'body' => 'hi there this is comment create by testing']);
    $response->assertStatus(200);
});

test('admin views not count', function () {
    $admin = userLogin();

    $article = Article::factory()->create();
    $this->actingAs($admin)->get(route('admin.article.show', $article));

    $this->assertDatabaseMissing('views',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});


