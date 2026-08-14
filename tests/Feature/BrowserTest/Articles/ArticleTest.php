<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('visit specific article', function () {
    UserLogin();

    $article = Article::factory()->create();

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('functionality check in articles', function () {
    UserLogin();
    $article = Article::factory()->create([
        'status' => ArticleStatus::PUBLISHED,
    ]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="like"]')
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="bookmark"]')
        ->press('Add Comment')
        ->type('#mountedActionSchema0\\.body', 'Hi there this is my first comment.')
        ->press('Submit')
        ->assertSee('Comment posted');

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

test('article update scheduled to draft clears published_at and duration', function () {
    UserLogin();

    $article = Article::factory()->create([
        'user_id' => auth()->id(),
        'status' => ArticleStatus::SCHEDULED,
        'published_at' => null,
        'duration' => now()->addHours(2),
    ]);

    visit(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->select('#form\\.status', ArticleStatus::DRAFT->value)
        ->click('Save changes')
        ->assertSee('Saved');

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
        'duration' => null,
    ]);
});

test('article update draft to published sets published_at and clears duration', function () {
    UserLogin();

    $article = Article::factory()->create([
        'user_id' => auth()->id(),
        'status' => ArticleStatus::DRAFT,
        'published_at' => null,
        'duration' => null,
    ]);

    visit(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->select('#form\\.status', ArticleStatus::PUBLISHED->value)
        ->click('Save changes')
        ->assertSee('Saved');

    $article->refresh();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => $article->published_at,
        'duration' => null,
    ]);
});

test('article update published to scheduled clears published_at and keeps duration', function () {
    UserLogin();

    $article = Article::factory()->create([
        'user_id' => auth()->id(),
        'status' => ArticleStatus::PUBLISHED,
        'published_at' => now()->subHour(),
        'duration' => null,
    ]);

    $duration = now()->addHours(2);

    visit(route('filament.app.resources.articles.edit', [
        'record' => $article,
    ]))
        ->select('#form\\.status', ArticleStatus::SCHEDULED->value)
        ->fill('#form\\.duration', $duration->format('Y-m-d\TH:i'))
        ->click('Save changes')
        ->assertSee('Saved');

    $article->refresh();

    expect($article->status)
        ->toBe(ArticleStatus::SCHEDULED);

    expect($article->published_at)
        ->toBeNull();

    expect($article->duration)
        ->not->toBeNull();
});
