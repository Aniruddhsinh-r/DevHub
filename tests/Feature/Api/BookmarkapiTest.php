<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

// ----------------------------------------------------------------------
// POST /api/v1/article/{slug}/bookmark
// ----------------------------------------------------------------------

test('a guest cannot bookmark an article', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/bookmark");

    $response->assertStatus(401);
});

test('author can bookmark a published article', function () {
    apiActingAsAuthor(['article.bookmark']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/bookmark");

    $response->assertCreated()->assertJson(['message' => 'Article bookmarked successfully.']);
    $this->assertDatabaseHas('bookmarks', ['article_id' => $article->id]);
});

test('bookmarking an article twice returns a conflict', function () {
    apiActingAsAuthor(['article.bookmark']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $this->postJson("/api/v1/article/{$article->slug}/bookmark")->assertCreated();
    $response = $this->postJson("/api/v1/article/{$article->slug}/bookmark");

    $response->assertStatus(409);
});

test('user cannot bookmark their own article', function () {
    $user = apiActingAsAuthor(['article.bookmark']);
    $article = Article::factory()->create(['user_id' => $user->id, 'status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/bookmark");

    $response->assertForbidden();
});

test('user without permission cannot bookmark an article', function () {
    apiActingAsAuthor([]);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/bookmark");

    $response->assertForbidden();
});

test('bookmarking a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.bookmark']);

    $response = $this->postJson('/api/v1/article/does-not-exist/bookmark');

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/article/{slug}/remove
// ----------------------------------------------------------------------

test('author can remove article from bookmark', function () {
    apiActingAsAuthor(['article.bookmark']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $this->postJson("/api/v1/article/{$article->slug}/bookmark")->assertCreated();
    $response = $this->deleteJson("/api/v1/article/{$article->slug}/remove");

    $response->assertOk()->assertJson(['message' => 'Article bookmark removed successfully.']);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('removing a bookmark that does not exist returns a 404', function () {
    apiActingAsAuthor(['article.bookmark']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->deleteJson("/api/v1/article/{$article->slug}/remove");

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// GET /api/v1/user/bookmark
// ----------------------------------------------------------------------

test('a user can list their bookmarked articles', function () {
    $user = apiActingAsAuthor(['article.bookmark']);
    $article1 = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);
    $article2 = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $this->postJson("/api/v1/article/{$article1->slug}/bookmark")->assertCreated();
    $this->postJson("/api/v1/article/{$article2->slug}/bookmark")->assertCreated();

    $response = $this->getJson('/api/v1/user/bookmark');

    $response->assertOk();
    expect($response->json('bookmarks.data'))->toHaveCount(2);
});

test('a guest cannot list bookmarked articles', function () {
    $response = $this->getJson('/api/v1/user/bookmark');

    $response->assertStatus(401);
});