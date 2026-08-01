<?php

use App\Models\Article;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('visit specific article', function () {
    UserLogin();
 
    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee('example Article')
        ->assertSee($article->excerpt);
});
 
test('functionality check in articles', function () {
    UserLogin();
    $article = Article::factory()->create([
        'user_id' => auth()->id(),
        'status' => ArticleStatus::PUBLISHED,
    ]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('[data-test="like-button"]')
        ->click('[data-test="bookmark-button"]')
        ->fill('body', 'Hi there this is my first comment.')
        ->press('Comment')
        ->assertSee('Comment successfully posted.');
 
    $this->assertDatabaseHas('likes', ['article_id' => $article->id, 'user_id' => auth()->id()]);
    $this->assertDatabaseHas('bookmarks', ['article_id' => $article->id, 'user_id' => auth()->id()]);
    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'body' => 'Hi there this is my first comment.',
    ]);
});
 
test('guest cant view specific article', function () {
    $article = Article::factory()->create();
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertUrlIs(route('filament.app.auth.login'));
});
 
test('author can access his draft article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});
 
test('author can access his scheduled article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED, 'user_id' => $user->id]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});
 
test('user can not access other draft article', function () {
    UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee('403')
        ->assertDontSee($article->title);
});
 
test('user can not access other scheduled article', function () {
    UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee('403')
        ->assertDontSee($article->title);
});
 
test('nonexistent article returns not found', function () {
    UserLogin();
 
    visit('/articles/this-slug-does-not-exist')
        ->assertSee('404');
});
 
test('viewing an article does not increment the view count for the author', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id,
        'status' => ArticleStatus::PUBLISHED,
        'view_count' => 0,
    ]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]));
 
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'view_count' => 0,
    ]);
});