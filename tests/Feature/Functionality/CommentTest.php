<?php

use App\Enums\ArticleStatus;
use Livewire\Livewire;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('guest cant access article page for comment', function () {
    $article = Article::factory()->create();

    $this->get(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertRedirect(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('Guest cant post comment', function () {
    $article = Article::factory()->create();
 
    Livewire::test('post-comment', ['article' => $article])
        ->set('body', 'hi there this is comment create by testing')
        ->call('postComment');
 
    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'body' => 'hi there this is comment create by testing',
    ]);
});
 
test('admin cant post comment', function () {
    $article = Article::factory()->create();
    $admin = AdminLogin();
 
    Livewire::actingAs($admin)
        ->test('post-comment', ['article' => $article])
        ->set('body', 'hi there this is comment create by testing')
        ->call('postComment');
 
    $this->assertDatabaseMissing('comments', [
        'user_id' => $admin->id,
    ]);
});
 
test('user can post comment', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $user = UserLogin();
 
    Livewire::actingAs($user)
        ->test('post-comment', ['article' => $article])
        ->set('body', 'hi there this is comment create by testing')
        ->call('postComment')
        ->assertHasNoErrors();
 
    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);
});

test('comment posted only on published article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);
 
    Livewire::actingAs($user)
        ->test('post-comment', ['article' => $article])
        ->set('body', 'trying to comment on my own draft')
        ->call('postComment');
 
    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'body' => 'trying to comment on my own draft',
    ]);
});
 
test('comment box is visible on published article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    Livewire::actingAs($user)
        ->test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee('Join the discussion');
});
 
test('comment is hide on unpublished article or its owner', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);
 
    Livewire::actingAs($user)
        ->test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee('Join the discussion');
});