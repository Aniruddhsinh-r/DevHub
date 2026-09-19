<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/UserLogin.php';

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
        ->assertNotPresent('button[wire\\:click*="mountAction"][wire\\:click*="postComment"]');
});

test('comment does not appear on schedule article', function () {
    $user = UserLogin();

    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED, 'user_id' => $user->id]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->assertNotPresent('button[wire\\:click*="mountAction"][wire\\:click*="postComment"]');
});

test('replying to a top level comment creates a level 2 reply', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_1"]')
        ->type('#mountedActionSchema0\\.body', 'a genuine reply')
        ->press('Submit')
        ->assertSee('Reply posted');

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'parent_id' => $comment->id,
        'body' => 'a genuine reply',
    ]);
});

test('check comment reply validation test One below Maximum.', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);
    $reply = str_repeat('A', 499);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_1"]')
        ->type('#mountedActionSchema0\\.body', $reply)
        ->press('Submit')
        ->assertSee('Reply posted');

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'parent_id' => $comment->id,
        'body' => $reply,
    ]);
});

test('check comment reply validation test maximum allowed.', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);
    $reply = str_repeat('A', 500);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_1"]')
        ->type('#mountedActionSchema0\\.body', $reply)
        ->press('Submit')
        ->assertSee('Reply posted');

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'parent_id' => $comment->id,
        'body' => $reply,
    ]);
});

test('check comment reply validation test one below minimum.', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_1"]')
        ->type('#mountedActionSchema0\\.body', '')
        ->press('Submit');

    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'parent_id' => $comment->id,
    ]);
});

test('check comment reply validation test One above Maximum.', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);
    $reply = str_repeat('A', 501);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_1"]')
        ->type('#mountedActionSchema0\\.body', $reply)
        ->press('Submit')
        ->assertSee('Reply posted');

    $this->assertDatabaseMissing('comments', [
        'article_id' => $article->id,
        'parent_id' => $comment->id,
        'body' => $reply,
    ]);
});

test('replying to 3 comment on article', function () {
    UserLogin();
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $level1 = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => null]);
    $level2 = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => $level1->id]);
    $level3 = Comment::factory()->create(['article_id' => $article->id, 'parent_id' => $level2->id]);

    visit(route('filament.app.resources.articles.view', ['record' => $article]))
        ->click('button[wire\\:click*="mountAction"][wire\\:click*="reply_3"]')
        ->type('#mountedActionSchema0\\.body', 'trying to go one level deeper')
        ->press('Submit')
        ->assertSee('Reply posted');

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'parent_id' => $level2->id,
        'body' => "@{$level3->user->name} trying to go one level deeper",
    ]);
});
