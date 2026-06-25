<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('user can update his article', function () {
    $user = userLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id
    ]);

    Livewire::test('livewirecomponent.article.edit-article',['article' => $article])
        ->set('title','first update testing article.')
        ->set('excerpt','Create article using test, and perform update.')
        ->set('body','test body content att least 30 character long as i decide test body.')
        ->set('status',ArticleStatus::PUBLISHED)
        ->call('update')
        ->assertRedirect(route('publishedarticle'));

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'first update testing article.',
        'excerpt' => 'Create article using test, and perform update.',
    ]);
});

test('user cannot update others article', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    $updateResponse = $this->actingAs($user)->get(route('articles.edit', $article), [
        'title' => 'try to update unauthorized article.',
        'excerpt' => 'try to update unauthorized article using test, and perform update.',
        'body' => $article->body,
        'category_id' => $article->category_id,
        'status' => $article->status->value,
    ]);

    $updateResponse->assertStatus(403);

    $this->assertDatabaseMissing('articles', [
        'title' => 'try to update unauthorized article.',
        'excerpt' => 'try to update unauthorized article using test, and perform update.',
    ]);
});
