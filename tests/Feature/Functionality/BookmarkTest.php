<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;

require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';
uses(RefreshDatabase::class);

test('guest cant access article page for bookmark', function () {
    $article = Article::factory()->create();

    $this->get(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertRedirect(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('user can bookmark article', function () {
    $user = UserLogin();
    $article = Article::factory()->create();

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertActionVisible('bookmark')
        ->callAction('bookmark');

    $this->assertDatabaseHas('bookmarks', ['article_id' => $article->id, 'user_id' => $user->id]);
});

test('user can bookmark but not twice', function () {
    $user = UserLogin();
    $article = Article::factory()->create();

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction('bookmark');

    $this->assertDatabaseHas('bookmarks', ['article_id' => $article->id, 'user_id' => $user->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction('bookmark');

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id, 'user_id' => $user->id]);
});

test('author cant see the bookmark action on their own article', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'status' => ArticleStatus::PUBLISHED,
        'user_id' => $user->id,
    ]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertActionHidden('bookmark');
});

test('user cant bookmark draft article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee('Bookmark');

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id, 'user_id' => $user->id]);
});

test('admin cant see bookmark action on article', function () {
    $admin = AdminLogin();
    $article = Article::factory()->create();

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertActionHidden('bookmark');

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id, 'user_id' => $admin->id]);
});
