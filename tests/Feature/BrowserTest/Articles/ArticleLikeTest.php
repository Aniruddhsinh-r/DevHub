<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/UserLogin.php';
require_once __DIR__.'/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('User can like article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('[data-test="like-button"]')
        ->assertSee('1 Like');
 
    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});
 
test('User can unlike article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('[data-test="like-button"]')
        ->assertSee('1 Like');
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('[data-test="like-button"]')
        ->assertSee('0 Like');
 
    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});
 
test('admin cant access article like page', function () {
    $admin = AdminLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee('403');
 
    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
        'article_id' => $article->id,
    ]);
});
 
test('guest cant like article', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertUrlIs(route('filament.app.auth.login'));
 
    $this->assertDatabaseMissing('likes', [
        'article_id' => $article->id,
    ]);
});