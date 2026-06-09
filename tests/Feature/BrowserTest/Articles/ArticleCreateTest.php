<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('it belongs to a user', function () {
    $idea = Article::factory()->create();
    expect($idea->user)->toBeInstanceOf(User::class);
});

test('article create', function () {
    userLogin();
    $category = Category::factory()->create();

    visit('/home')
    ->click('Create Article')
    ->fill('title', 'hither aniruddhsinh')
    ->select('category_id', $category->id)
    ->select('status', 'published')
    ->fill('excerpt', 'this is test case for checking hope this work.')
    ->fill('body', 'Test case new Article body testing for Amazing article creating and test dummy body data.')
    ->press('@submitBTN')
    ->assertRoute('articles.index');

    $this->assertDatabaseHas('articles', [
        'title' => 'hither aniruddhsinh',
        'status' => 'published',
    ]);
});

test('article update', function () {
    userLogin();
    $article = Article::factory()->create([
        'user_id' => auth()->id(),
    ]);

    visit('/articles/myarticle')
    ->click('[dusk="edit-article-' . $article->id . '"]')
    ->fill('title', 'hither aniruddhsinh')
    ->select('status', 'published')
    ->fill('excerpt', 'this article is update by browser test case.')
    ->press('@submitBTN')
    ->assertRoute('publishedarticle')
    ->assertSee('Article updated successfully.');

    $this->assertDatabaseHas('articles', [
        'title' => 'hither aniruddhsinh',
        'status' => 'published',
    ]);
});

test('article delete', function () {
    userLogin();
    $article = Article::factory()->create([
        'title' => 'article create for delete browser test checking',
        'user_id' => auth()->id()
    ]);

   visit('/articles/myarticle')
    ->click('[dusk="delete-article-' . $article->id . '"]')
    ->assertDontSee('article create for delete browser test checking');

    sleep(1);

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
    ]);
});


