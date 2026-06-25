<?php

use App\Models\Article;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('visite specific articles', function () {
    UserLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/articles?search=example Article')
    ->click('example Article')
    ->assertRoute('articles.show',['article' => $article]);

    expect($article->fresh()->view_count)->toBe(1);
});

test('functionality check in articles.',function () {
    UserLogin();
    $article = Article::factory()->create([
        'user_id' => auth()->id()
    ]);

    visit(route('articles.show',$article))
        ->press('@like-button')
        ->press('@bookmark-button')
        ->fill('body','Hi there this is my first comment.')
        ->press('Comment')
        ->assertSee('Comment successfully posted.')
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

test('author can access his draft article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT,'user_id'=>$user->id]);
    visit(route('articles.show', $article))
    ->assertSee($article->title)
    ->assertSee($article->excerpt);
});

test('author can access his scheduled article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::SCHEDULED,'user_id'=>$user->id]);
    visit(route('articles.show', $article))
    ->assertSee($article->title)
    ->assertSee($article->excerpt);
});

test('user can not access other draft article', function() {
    UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT]);
    visit(route('articles.show', $article))
    ->assertDontSee($article->title)
    ->assertDontSee($article->excerpt)
    ->assertRoute('articles.index')
    ->assertSee('The article you are looking for is not available.');
});

test('user can not access other scheduled article', function() {
    UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::SCHEDULED]);
    visit(route('articles.show', $article))
    ->assertDontSee($article->title)
    ->assertDontSee($article->excerpt)
    ->assertRoute('articles.index')
    ->assertSee('The article you are looking for is not available.');
});
