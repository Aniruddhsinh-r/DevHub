<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\Pages\EditArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';

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