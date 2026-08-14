<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/UserLogin.php';
require_once __DIR__.'/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('User can bookmark article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="bookmark"]')
        ->assertSee('Saved');

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('User can remove articles from bookmark', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="bookmark"]')
        ->assertSee('Saved');

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="bookmark"]')
        ->assertSee('Bookmark');

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('bookmarked article shows up on the bookmark page', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['excerpt' => 'hi this is excerpt wer.', 'title' => 'A bookmarked article']);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="bookmark"]');

    visit('/articles/bookmarks')
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('admin cant visit bookmark articles page', function () {
    $admin = AdminLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee('403');

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $admin->id,
        'article_id' => $article->id,
    ]);
});

test('guest cant bookmark article', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertUrlIs(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('bookmarks', [
        'article_id' => $article->id,
    ]);
});
