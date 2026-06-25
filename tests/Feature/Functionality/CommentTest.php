<?php

use Livewire\Livewire;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('Guest cant post comment', function () {
    $article = Article::factory()->create();

    Livewire::test('livewirecomponent.post-comment', ['article' => $article])
    ->set('body', 'hi there this is comment create by testing')
    ->call('postComment')
    ->assertRedirect(route('/'));

    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'body' => 'guest try to comment on articles',
    ]);
});

test('guest cant access article page for comment', function () {
    $article = Article::factory()->create();

    $this->get(route('articles.show', $article))
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

test('admin cant post comment', function () {
    $article = Article::factory()->create();
    $admin = AdminLogin();

    Livewire::test('livewirecomponent.post-comment', ['article' => $article])
    ->set('body', 'hi there this is comment create by testing')
    ->call('postComment')
    ->assertRedirect(route('/'));

    $this->assertDatabaseMissing('comments', [
        'user_id' => $admin->id,
    ]);
});

test('user can post comment', function () {
    $article = Article::factory()->create();
    $user = UserLogin();

    Livewire::test('livewirecomponent.post-comment', ['article' => $article])
    ->set('body', 'hi there this is comment create by testing')
    ->call('postComment')
    ->assertStatus(200);

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'user_id' => $user->id,
        'body' => 'hi there this is comment create by testing',
    ]);
});
