<?php

use App\Models\Article;
use App\Models\User;

require_once __DIR__.'/../Helpers/userLogin.php';

test('visite specific articles', function () {
    userLogin();

    $article = Article::factory()->create([
        'title' => 'example Article9',
        'user_id' => 13,
        'category_id' => 2
    ]);

    // visit('/articles')
    // ->assertSee('example Article7');

    visit('/articles?search=example Article9')
    ->click('example Article9')
    ->assertPathIs('/articles/' . $article->id);

    expect($article->fresh()->view_count)->toBe(1);
});

test('functionality check in articles.',function () {
    userLogin();
    $article = Article::factory()->create([
        'category_id' => 5,
        'user_id' => 5
    ]);

    visit(route('articles.show',$article->id))
        ->press('@like-button')
        ->press('@bookmark-button')
        ->fill('body','Hi there this is my first comment.')
        ->press('@PostComment')
        ;

        $this->assertDatabaseHas('likes',['article_id'=>$article->id , 'user_id'=>22]);
        $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>22]);
        // $this->assertDatabaseHas('comments',['article_id'=>1 , 'user_id'=>22, 'body'=>'Hi there this is my first comment.']);
});

test('guest cant view specific article', function () {
    $article = Article::find(12);

    visit(route('articles.show', $article->id))
    ->assertRoute('login');
});
