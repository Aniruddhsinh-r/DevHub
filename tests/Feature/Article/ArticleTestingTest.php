<?php

use App\Models\Article;
use Livewire\Livewire;
use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/../Helpers/UserLogin.php';
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('user can comment like and bookmark article', function () {
    $user = userLogin();

    $article = Article::factory()->create();
    $response = $this->actingAs($user)->get(route('articles.show', $article));

    Livewire::test(route('filament.app.resources.articles.view', ['record' => $article]))
        ->set('body', 'hi there this is comment create by testing')
        ->call('postComment')
        ->assertStatus(200);

    Livewire::test(route('filament.app.resources.articles.view', ['record' => $article]))
        ->call('toggleBookmark')
        ->assertDispatched('live-notification', message: 'article bookmark');

    Livewire::test(route('filament.app.resources.articles.view', ['record' => $article]))
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

test('admin viewing article does not create a view record', function () {
    $admin = AdminLogin();

    $article = Article::factory()->create();

    Livewire::actingAs($admin)
        ->test(ViewArticle::class, [
            'record' => $article->getRouteKey(),
        ])
        ->assertSuccessful();

    $this->assertDatabaseMissing('views', [
        'article_id' => $article->id,
        'user_id' => $admin->id,
    ]);
});

test('guest cant access article page', function () {
    $article = Article::factory()->create();

    $this->get(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertRedirect(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('author can access his draft article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::DRAFT,'user_id'=>$user->id]);
    
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('author can access his scheduled article', function() {
    $user = UserLogin();

    $article = Article::factory()->create(['status'=>ArticleStatus::SCHEDULED,'user_id'=>$user->id]);
    
    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($article->title)
        ->assertSee($article->excerpt);
});

test('user can not access other draft article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertDontSee($article->title)
        ->assertDontSee($article->excerpt)
        ->assertForbidden();
});

test('user can not access other scheduled article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertForbidden();
});
