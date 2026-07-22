<?php

use App\Models\Article;
use App\Models\Category;
use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Pages\ViewArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('guest is redirected to login when visiting the articles page', function () {
    $this->get('/admin/articles')
        ->assertRedirect('/admin/login');
});

test('admin can render the articles list page', function () {
    AdminLogin();

    Livewire::test(ListArticles::class)
        ->assertSuccessful();
});

test('admin search articles', function () {
    AdminLogin();

    $article = Article::factory()->create([
        'title' => 'example article',
    ]);
    Article::factory()->create([
        'title' => 'another article',
    ]);

    Livewire::test(ListArticles::class)
        ->searchTable('example article')
        ->assertCanSeeTableRecords([$article])
        ->assertCanNotSeeTableRecords(Article::where('title', 'another article')->get());
});

test('admin can view an article without adding to its view count', function () {
    $admin = AdminLogin();

    $article = Article::factory()->create([
        'title' => 'example article',
    ]);

    Livewire::test(ViewArticle::class, ['record' => $article->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('example article');

    $this->assertDatabaseMissing('views', ['user_id' => $admin->id]);
});

test('admin can edit an article', function () {
    AdminLogin();

    $article = Article::factory()->create([
        'title' => 'old title',
        'excerpt' => 'old excerpt',
    ]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm([
            'title' => 'updated title',
            'excerpt' => 'updated excerpt',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'updated title',
        'excerpt' => 'updated excerpt',
    ]);
});

test('admin can delete an article', function () {
    AdminLogin();

    $article = Article::factory()->create();

    Livewire::test(ListArticles::class)
        ->callTableAction('delete', $article);

    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});

test('admin can restore a deleted article', function () {
    AdminLogin();

    $article = Article::factory()->create();
    $article->delete();

    Livewire::test(ListArticles::class)
        ->filterTable('trashed')
        ->callTableAction('restore', $article);

    $this->assertDatabaseHas('articles', ['id' => $article->id, 'deleted_at' => null]);
});

test('author cannot access admin articles page', function () {
    UserLogin();

    $this->get('/admin/articles')
        ->assertForbidden();
});