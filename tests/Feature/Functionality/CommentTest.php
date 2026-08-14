<?php

use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/UserLogin.php';
require_once __DIR__.'/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('guest cant access article page for comment', function () {
    $article = Article::factory()->create();

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertStatus(403);

    $this->assertDatabaseMissing('comments', ['article_id' => $article->id]);
});

test('admin cant post comment', function () {
    AdminLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee('Add Comment');

});

test('author cant post an empty comment', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction(
            TestAction::make('postComment')->schemaComponent(''),
            data: ['body' => ''],
        )
        ->assertHasActionErrors(['body' => 'required']);

    $this->assertDatabaseMissing('comments', ['article_id' => $article->id]);
});

test('author can post comment on published article', function () {
    Notification::fake();

    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction(
            TestAction::make('postComment')->schemaComponent(''),
            data: ['body' => 'hi there this is comment created by testing'],
        )
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'parent_id' => null,
        'body' => 'hi there this is comment created by testing',
    ]);

    Notification::assertSentTo($article->user, CommentNotification::class);
});

test('author is not notified when commenting on their own article', function () {
    Notification::fake();

    $user = UserLogin();
    $article = Article::factory()->create([
        'status' => ArticleStatus::PUBLISHED,
        'user_id' => $user->id,
    ]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->callAction(
            TestAction::make('postComment')->schemaComponent(''),
            data: ['body' => 'commenting on my own article'],
        )
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    Notification::assertNothingSent();
});

test('comment posted only on published article', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'status' => ArticleStatus::DRAFT,
        'user_id' => $user->id,
    ]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee('Add Comment');
});

test('comment section is visible on the article view page', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee('Comments')
        ->assertSee('Add Comment');
});

test('comment count is shown on the article view page', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    Comment::factory()->count(3)->create(['article_id' => $article->id]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee('3 comments');
});
