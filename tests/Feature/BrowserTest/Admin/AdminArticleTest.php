<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/adminLogin.php';
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('Admin search and see article', function () {
    adminLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/admin/articles?search=example+article')
    ->assertSee('example Article')
    ->click('example Article')
    ->assertRoute('admin.article.show',['article' => $article]);
});

test('Admin view not count', function () {
    $admin = adminLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/admin/articles?search=example+article')
    ->assertSee('example Article')
    ->click('example Article')
    ->assertRoute('admin.article.show', ['article' => $article]);
    $this->assertDatabaseMissing('views',['user_id' => $admin->id]);
});

test('guest cant access admin article page', function () {
    visit('/admin/articles?search=example+article')
    ->assertRoute('login');
});

test('Author cant access admin article page', function () {
    userLogin();

    visit('/admin/articles')
    ->assertSee('403')
    ->assertSee('Forbidden');
});
