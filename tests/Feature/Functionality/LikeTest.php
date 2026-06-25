<?php

use App\Models\Article;
use App\Models\User;
use App\Enums\ArticleStatus;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/adminLogin.php';
require_once __DIR__ . '/../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('user can like but not twice', function () {
    $article = Article::factory()->create();
    $user = userLogin();

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
    $admin = adminLogin();
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
    ->call('toggleLike')
    ->assertRedirect(route('/'))
    ->assertSessionHas('error', 'Only Author can Like article.');

    $this->assertDatabaseMissing('likes', [
        'user_id' => $admin->id,
    ]);
});

test('user cant like draft article', function () {
    $user = userLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT,'user_id' => $user->id]);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleLike')
        ->assertDispatched('live-notification', message: 'You cant like draft articles.');

    $this->assertDatabaseMissing('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
});
