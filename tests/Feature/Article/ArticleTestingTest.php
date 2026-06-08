<?php

use App\Models\Article;
use App\Models\User;

test('check article functionalitys', function () {
    $user = User::find(22);
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
    $admin = User::find(1);
    $article = Article::find(4);
    $response = $this->actingAs($admin)->get(route('admin.article.show', $article->id));

    $this->assertDatabaseMissing('views',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});


