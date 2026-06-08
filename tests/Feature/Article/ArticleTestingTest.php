<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('check article functionalitys', function () {
    $user = User::factory()->create([
        'role' => 'author',
    ]);
    $article = Article::factory()->create();
    $response = $this->actingAs($user)->get(route('articles.show', $article->id));

    $this->actingAs($user)->post(route('post.comment',$article->id), [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);

    $this->actingAs($user)->post(route('articles.bookmark',$article->id));

    $this->actingAs($user)->post(route('articles.like',$article->id));

    $this->assertDatabaseHas('views',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('comments',['article_id'=>$article->id ,
        'user_id'=>$user->id,
        'body' => 'hi there this is comment create by testing']);
    $response->assertStatus(200);
});

test('admin views not count', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    $article = Article::factory()->create();
    $this->actingAs($admin)->get(route('admin.article.show', $article->id));

    $this->assertDatabaseMissing('views',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});


