<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/UserLogin.php';
require_once __DIR__.'/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('it belongs to a user', function () {
    $idea = Article::factory()->create();
    expect($idea->user)->toBeInstanceOf(User::class);
});

test('article create', function () {
    UserLogin();
    $category = Category::factory()->create();

    visit('/articles/create')
        ->fill('#form\\.title', 'hither aniruddhsinh')
        ->click('.fi-select-input-btn')
        ->click($category->name)
        ->select('#form\\.status', ArticleStatus::PUBLISHED->value)
        ->fill('#form\\.excerpt', 'this is test case for checking hope this work perfectly.')
        ->fill('#form\\.body', 'Test case new Article body testing for Amazing article creating and test dummy body data.')
        ->click('#key-bindings-1')
        ->assertUrlIs(route('filament.app.resources.articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'hither aniruddhsinh',
        'status' => 'published',
    ]);
});

test('article update', function () {
    UserLogin();
    $article = Article::factory()->create([
        'user_id' => auth()->id(),
    ]);

    visit(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->fill('#form\\.title', 'hither aniruddhsinh')
        ->select('#form\\.status', ArticleStatus::PUBLISHED->value)
        ->fill('#form\\.excerpt', 'this article is updated by browser test case.')
        ->click('Save changes')
        ->assertSee('Saved');

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'hither aniruddhsinh',
        'status' => 'published',
    ]);
});

test('article delete', function () {
    UserLogin();
    $article = Article::factory()->create([
        'title' => 'article create for delete browser test checking',
        'user_id' => auth()->id(),
    ]);

    visit(route('filament.app.resources.articles.edit', ['record' => $article]))
        ->click('Delete')
        ->click('button[type="submit"][wire\\:target="callMountedAction"]')
        ->assertSee('Deleted');

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
    ]);
});

test('article validation test', function () {
    UserLogin();
    $category = Category::factory()->create();

    visit('/articles/create')
        ->fill('#form\\.title', 'hello')
        ->click('.fi-select-input-btn')
        ->click($category->name)
        ->select('#form\\.status', ArticleStatus::PUBLISHED->value)
        ->fill('#form\\.excerpt', 'less then 20')
        ->fill('#form\\.body', 'less then 30')
        ->click('#key-bindings-1');

    $this->assertDatabaseMissing('articles', [
        'user_id' => auth()->id(),
        'title' => 'hello',
    ]);
});
