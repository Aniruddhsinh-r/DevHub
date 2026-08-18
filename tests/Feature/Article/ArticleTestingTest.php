<?php

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/UserLogin.php';
require_once __DIR__.'/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('guest cant access article page', function () {
    $article = Article::factory()->create();

    $this->get(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertRedirect(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('author can access his draft article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('author can access his scheduled article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED, 'user_id' => $user->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('user can not access other draft article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee($article->title)
        ->assertDontSee($article->excerpt)
        ->assertForbidden();
});

test('user can not access other scheduled article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertForbidden();
});

test('viewing an article twice by the same user only increments view_count once', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])->assertSuccessful();
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])->assertSuccessful();

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'view_count' => 1]);
});

test('two different users viewing an article increments view_count for each', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])->assertSuccessful();

    $secondViewer = User::factory()->create();
    $secondViewer->assignRole(UserRole::AUTHOR);
    $this->actingAs($secondViewer);
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])->assertSuccessful();

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'view_count' => 2]);
});
