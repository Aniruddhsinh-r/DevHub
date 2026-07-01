<?php

use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('user can like but not twice', function () {
    $article = Article::factory()->create();
    $user = UserLogin();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleLike')
        ->assertDispatched('live-notification', message: 'article like');

    $this->assertDatabaseHas('likes', [
        'article_id' => $article->id,
        'user_id' => $user->id,
    ]);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleLike')
        ->assertDispatched('live-notification', message: 'article unlike');

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'article_id' => $article->id,
    ]);
});

test('admin cant like', function () {
    $admin = AdminLogin();
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
    ->call('toggleLike')
    ->assertForbidden();

    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
    ]);
});

test('user cant like draft article', function () {
    $user = UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT,'user_id' => $user->id]);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleLike')
        ->assertForbidden();
        
    $this->assertDatabaseMissing('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
