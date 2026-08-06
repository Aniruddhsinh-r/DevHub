<?php

use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
use Filament\Actions\Testing\TestAction;

require_once __DIR__ . '/../Helpers/AdminLogin.php';
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('guest cant access article page for like', function () {
    $article = Article::factory()->create();
 
    $this->get(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertRedirect(route('filament.app.auth.login'));
 
    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
});

test('user can like but not twice', function () {
    $article = Article::factory()->create();
    $user = UserLogin();

    $this->actingAs($user);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey(),])
        ->callAction(TestAction::make('like'));

    $this->assertDatabaseHas('likes', [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey(),])
        ->callAction(TestAction::make('like'));

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('admin cant see the like action', function () {
    AdminLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertActionHidden('like');
});

test('author cant see the like action on their own article', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'status' => ArticleStatus::PUBLISHED,
        'user_id' => $user->id,
    ]);
 
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertActionHidden('like');
});

test('user cant like draft article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey(),])
        ->assertDontSee('Like');
        // ->assertForbidden();

    $this->assertDatabaseMissing('likes', ['article_id' => $article->id, 'user_id' => $user->id]);
});

test('like button label reflects like state and count', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee('Like 0');
 
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction('like');
 
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee('Unlike 1');
});