<?php

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Filament\App\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('user can update his article', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id,
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm([
            'title' => 'first update testing article.',
            'excerpt' => 'Create article using test, and perform update.',
            'body' => 'test body content att least 30 character long as i decide test body.',
            'status' => ArticleStatus::PUBLISHED,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
    ]);
});

test('user cannot update others article', function () {
    $user = UserLogin();
    $article = Article::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->assertForbidden();

    $this->assertDatabaseMissing('articles', [
        'title' => 'try to update unauthorized article.',
    ]);
});

test('guest can not access article edit page', function () {
    $article = Article::factory()->create();

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertSee(403);
});

test('update published status make published_at and duration field clear', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id,
        'status' => ArticleStatus::SCHEDULED,
        'published_at' => null,
        'duration' => now()->addHours(2),
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['status' => ArticleStatus::PUBLISHED])
        ->call('save')
        ->assertHasNoFormErrors();

    $article->refresh();
    expect($article->status)->toBe(ArticleStatus::PUBLISHED);
    expect($article->published_at)->not->toBeNull();
    expect($article->duration)->toBeNull();
});

test('updating a title that already exist gets a unique suffixed slug.', function () {
    $user = UserLogin();
    Article::factory()->create(['title' => 'Alpha Title', 'slug' => 'alpha-title']);
    $article = Article::factory()->create(['user_id' => $user->id, 'title' => 'Beta Title', 'slug' => 'beta-title']);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['title' => 'Alpha Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'slug' => 'alpha-title-2']);
});

test('unpublishing an article removes its likes and bookmarks', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['user_id' => $user->id, 'status' => ArticleStatus::PUBLISHED, 'published_at' => now()]);

    $liker = User::factory()->create();
    $liker->assignRole(UserRole::AUTHOR);
    $article->likes()->create(['user_id' => $liker->id]);
    $article->bookmarks()->attach($liker->id);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['status' => ArticleStatus::DRAFT])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});
