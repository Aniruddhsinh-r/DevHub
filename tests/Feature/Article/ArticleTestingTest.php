<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('user can comment like and bookmark article', function () {
    $user = userLogin();

    $article = Article::factory()->create();
    $response = $this->actingAs($user)->get(route('articles.show', $article));

    Livewire::test('livewirecomponent.post-comment', ['article' => $article])
        ->set('body', 'hi there this is comment create by testing')
        ->call('postComment')
        ->assertStatus(200);

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'article bookmark');

    Livewire::test('livewirecomponent.article.show-article',['article' => $article])
        ->call('toggleLike')
        ->assertDispatched('live-notification', message: 'article like');

    $this->assertDatabaseHas('views',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('likes',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('bookmarks',['article_id'=>$article->id , 'user_id'=>$user->id]);
    $this->assertDatabaseHas('comments',['article_id'=>$article->id ,
        'user_id'=>$user->id,
        'body' => 'hi there this is comment create by testing']);
    $response->assertStatus(200);
});

test('admin views not count', function () {
    $admin = AdminLogin();

    $article = Article::factory()->create();
    Livewire::test('livewirecomponent.admin.show-article',['article' => $article]);

    $this->assertDatabaseMissing('views',['article_id'=>$article->id , 'user_id'=>$admin->id]);
});

test('guest cant access article page', function () {
    $article = Article::factory()->create();

    $this->get(route('articles.show', $article))
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('bookmarks',['article_id'=>$article->id]);
});

// test('admin cant access functional article page', function () {
//     $admin = AdminLogin();
//     $article = Article::factory()->create();

//     $this->actingAs($admin)->get(route('articles.show', $article))
//         ->assertStatus(403);

//     $this->assertDatabasemissing('views',['article_id'=>$article->id , 'user_id'=>$admin->id]);
// });

test('author can access his draft article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT,'user_id'=>$user->id]);
    $this->actingAs($user)->get(route('articles.show', $article))
    ->assertSee($article->title)
    ->assertSee($article->excerpt);
});

test('author can access his scheduled article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::SCHEDULED,'user_id'=>$user->id]);
    $this->actingAs($user)->get(route('articles.show', $article))
    ->assertSee($article->title)
    ->assertSee($article->excerpt);
});

test('user can not access other draft article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT]);
    $this->actingAs($user)->get(route('articles.show', $article))
    ->assertDontSee($article->title)
    ->assertDontSee($article->excerpt)
    ->assertRedirect(route('articles.index'))
    ->assertSessionHas('error', 'The article you are looking for is not available.');
});

test('user can access his scheduled article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>'scheduled']);
    $this->actingAs($user)->get(route('articles.show', $article))
    ->assertDontSee($article->title)
    ->assertDontSee($article->excerpt)
    ->assertRedirect(route('articles.index'))
    ->assertSessionHas('error', 'The article you are looking for is not available.');
});
