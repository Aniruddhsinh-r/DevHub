<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

test('a guest cannot like an article', function () {
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertStatus(401);
});

test('an authorized user can like a published article', function () {
    apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertCreated()->assertJson(['message' => 'Article liked successfully.']);
    $this->assertDatabaseHas('likes', ['article_id' => $article->id]);
});

test('liking an article twice returns a conflict', function () {
    $user = apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $this->postJson("/api/v1/article/{$article->slug}/like")->assertCreated();
    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertStatus(409);
});

test('a user cannot like their own article', function () {
    $user = apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['user_id' => $user->id, 'status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertForbidden();
});

test('a user without permission cannot like an article', function () {
    apiActingAsAuthor([]);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertForbidden();
});

test('liking a draft article is forbidden', function () {
    apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    $response = $this->postJson("/api/v1/article/{$article->slug}/like");

    $response->assertForbidden();
});

test('liking a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.like']);

    $response = $this->postJson('/api/v1/article/does-not-exist/like');

    $response->assertNotFound();
});

test('a user can unlike a previously liked article', function () {
    apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $this->postJson("/api/v1/article/{$article->slug}/like")->assertCreated();
    $response = $this->deleteJson("/api/v1/article/{$article->slug}/dislike");

    $response->assertOk()->assertJson(['message' => 'Article unliked successfully.']);
    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
});

test('unliking an article that was never liked returns a 404', function () {
    apiActingAsAuthor(['article.like']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->deleteJson("/api/v1/article/{$article->slug}/dislike");

    $response->assertNotFound();
});

test('unliking a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.like']);

    $response = $this->deleteJson('/api/v1/article/does-not-exist/dislike');

    $response->assertNotFound();
});

test('prevents duplicate likes at the database level (race condition)', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['slug' => 'test-article']);

    DB::table('likes')->insert([
        'article_id' => $article->id,
        'user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('likes')->insert([
        'article_id' => $article->id,
        'user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    $this->assertDatabaseCount('likes', 1);
});
