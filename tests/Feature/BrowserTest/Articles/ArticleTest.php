<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('visite specific articles', function () {
    userLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/articles?search=example Article')
    ->click('example Article')
    ->assertRoute('articles.show',['article' => $article]);

    expect($article->fresh()->view_count)->toBe(1);
});

test('functionality check in articles.',function () {
    userLogin();
    $article = Article::factory()->create([
        'user_id' => auth()->id()
    ]);

    visit(route('articles.show',$article))
        ->press('@like-button')
        ->press('@bookmark-button')
        ->fill('body','Hi there this is my first comment.')
        ->press('@PostComment')
        ;

        $this->assertDatabaseHas('likes',['article_id'=>$article->id , 'user_id'=>auth()->id()]);
        $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>auth()->id()]);
        $this->assertDatabaseHas('comments',['article_id'=>$article->id , 'user_id'=>auth()->id(), 'body'=>'Hi there this is my first comment.']);
});

test('guest cant view specific article', function () {
    $article = Article::factory()->create();

    visit(route('articles.show', $article))
    ->assertRoute('login');
});
