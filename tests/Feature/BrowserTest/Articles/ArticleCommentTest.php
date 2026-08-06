<?php

use App\Models\Article;
use App\Enums\ArticleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('Author comment on article', function () {
    UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->press('Add Comment')
        ->type('#mountedActionSchema0\\.body', 'hello this comment created by browser comment testing.')
        ->press('Submit')
        ->assertSee('Comment posted');
 
    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'body' => 'hello this comment created by browser comment testing.',
    ]);
});
 
test('comment does not appear on draft article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $user->id]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertDontSee('button[wire\\:click*="mountAction"][wire\\:click*="postComment"]');
});
 
test('comment does not appear on schedule article', function () {
    $user = UserLogin();
 
    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED, 'user_id' => $user->id]);
 
    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertDontSee('button[wire\\:click*="mountAction"][wire\\:click*="postComment"]');
});