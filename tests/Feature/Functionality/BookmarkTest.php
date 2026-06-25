<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/userLogin.php';
require_once __DIR__ . '/../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('admin cant bookmark article', function () {
    $admin = adminLogin();
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertRedirect(route('/'))
        ->assertSessionHas('error', 'Only Author can bookmark article.');

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id, 'user_id'=>$admin->id]);
});

test('guest cant access article page for bookmark', function () {
    $article = Article::factory()->create();

    $this->get(route('articles.show', $article))
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

test('Guest cant bookmark article', function () {
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertRedirect(route('/'))
        ->assertSessionHas('error', 'Only Author can bookmark article.');

    $this->assertDatabaseMissing('bookmarks', [
        'article_id' => $article->id,
    ]);
});

test('user can bookmark article', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'article bookmark');

    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user can bookmark but not twice', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'article bookmark');

    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'remove from bookmark');
    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});

test('user cant bookmark draft article', function () {
    $user = userLogin();
    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT,'user_id'=>$user->id]);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'You cant bookmark draft articles');

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
