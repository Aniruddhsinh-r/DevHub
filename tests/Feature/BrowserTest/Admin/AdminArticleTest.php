<?php

use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('Admin fetch admin details', function () {
    $article = Article::factory()->create();
    AdminLogin();

    visit('/admin/articles')
        ->assertSee($article->title);
});

test('Admin search and see article', function () {
    AdminLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/admin/articles/'.$article->slug)
        ->assertSee('example Article');
});

test('Admin view not count', function () {
    $admin = AdminLogin();

    $article = Article::factory()->create([
        'title' => 'example Article',
    ]);

    visit('/admin/articles')
        ->assertSee('example Article')
        ->press('example Article')
        ->assertPathIs('/admin/articles/'.$article->slug)
        ->assertSee($article->excerpt)
        ->assertSee($article->body);

    $this->assertDatabaseMissing('views', ['user_id' => $admin->id]);
});

test('guest cant access admin article page', function () {
    visit('/admin/articles?search=example+article')
        ->assertPathIs('/admin/login');
});

test('Author cant access admin article page', function () {
    UserLogin();

    visit('/admin/articles')
        ->assertSee('403')
        ->assertSee('Forbidden');
});

test('admin sees forbidden opening a trashed article edit URL', function () {
    $article = Article::factory()->create();
    $article->delete();
    AdminLogin();

    visit('/admin/articles/'.$article->slug.'/edit')
        ->assertSee('403')
        ->assertSee('Forbidden');
});
