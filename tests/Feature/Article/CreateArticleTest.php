<?php

use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\Pages\CreateArticle;
use App\Filament\App\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/AdminLogin.php';
require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('guest is redirected to login when visiting the create article page', function () {
    $this->get(route('filament.app.resources.articles.create'))
        ->assertRedirect(route('filament.app.auth.login'));
});

test('admin cannot access create article page', function () {
    AdminLogin();

    $this->get(route('filament.app.resources.articles.create'))
        ->assertForbidden();

    $this->assertDatabaseMissing('articles', [
        'title' => null,
    ]);
});

test('user can create an article', function () {
    UserLogin();
    $category = Category::factory()->create();

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'category_id' => $category->id,
            'status' => ArticleStatus::PUBLISHED,
            'title' => 'first testing article.',
            'excerpt' => 'article created by kishan that gonna delete for purpose.',
            'body' => 'test body content att least 30 character long as i decide test body.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'title' => 'first testing article.',
        'excerpt' => 'article created by kishan that gonna delete for purpose.',
        'status' => ArticleStatus::PUBLISHED,
    ]);
});

test('user can delete his own article', function () {
    $user = UserLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id,
        'category_id' => Category::factory()->create()->id,
    ]);
    Comment::factory()->create(['article_id' => $article->id]);
    View::factory()->create(['article_id' => $article->id]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->callAction('delete');

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
        'user_id' => $user->id,
    ]);

    $this->assertSoftDeleted('comments', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('views', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('user cannot delete others article', function () {
    $user = UserLogin();
    $article = Article::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->assertForbidden();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'deleted_at' => null,
    ]);
});
