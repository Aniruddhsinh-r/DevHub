<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

test('a guest cannot comment on an article', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => 'Nice article!']);

    $response->assertStatus(401);
});

test('author can comment on a published article', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => 'Nice article!']);

    $response->assertCreated()->assertJsonPath('comment.body', 'Nice article!');
    $this->assertDatabaseHas('comments', ['article_id' => $article->id, 'body' => 'Nice article!']);
});

test('user without permission cannot comment', function () {
    apiActingAsAuthor([]);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => 'Nice article!']);

    $response->assertForbidden();
});

test('commenting on a draft article is forbidden', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => 'Nice article!']);

    $response->assertForbidden();
});

test('commenting on a schedule article is forbidden', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::SCHEDULED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => 'Nice article!']);

    $response->assertForbidden();
});

test('commenting requires a non-empty body', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment", ['body' => '']);

    $response->assertStatus(422)->assertJsonValidationErrors(['body']);
});

test('commenting on a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.comment']);

    $response = $this->postJson('/api/v1/article/does-not-exist/comment', ['body' => 'Nice article!']);

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// POST /api/v1/article/{slug}/comment/reply
// ----------------------------------------------------------------------

test('an authorized user can reply to a comment', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $article->id]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment/reply", [
        'body' => 'Thanks for reading!',
        'parent_id' => $comment->id,
    ]);

    $response->assertCreated()->assertJsonPath('comment.parent_id', $comment->id);
    $this->assertDatabaseHas('comments', ['parent_id' => $comment->id, 'body' => 'Thanks for reading!']);
});

test('replying fails when the parent comment does not belong to the article', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $otherArticle = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $comment = Comment::factory()->create(['article_id' => $otherArticle->id]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment/reply", [
        'body' => 'Thanks for reading!',
        'parent_id' => $comment->id,
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['parent_id']);
});

test('replying requires a valid parent_id', function () {
    apiActingAsAuthor(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/comment/reply", [
        'body' => 'Thanks for reading!',
        'parent_id' => 999999,
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['parent_id']);
});

test('replying to a comment on non-existent article returns 404', function () {
    apiActingAsAuthor(['article.comment']);

    $response = $this->postJson('/api/v1/article/does-not-exist/comment/reply', [
        'body' => 'Thanks for reading!',
        'parent_id' => 1,
    ]);

    $response->assertNotFound();
});
