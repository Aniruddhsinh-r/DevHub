<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

function validArticlePayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'A Sufficiently Long Article Title',
        'excerpt' => 'This is a short excerpt for the article.',
        'body' => str_repeat('This is the article body content. ', 3),
        'category_id' => Category::factory()->create()->id,
        'status' => ArticleStatus::DRAFT->value,
    ], $overrides);
}

test('a guest cannot create an article', function () {
    $response = $this->postJson('/api/v1/article/create', validArticlePayload());

    $response->assertStatus(401);
});

test('admin can create an article via the admin create route', function () {
    apiActingAsAdmin();

    $response = $this->postJson('/api/v1/admin/article/create', validArticlePayload());

    $response->assertCreated()->assertJson(['message' => 'Article created successfully.']);
});

test('user with permission can create an article', function () {
    apiActingAsAuthor(['article.create']);

    $response = $this->postJson('/api/v1/article/create', validArticlePayload(['title' => 'My First Article']));

    $response->assertCreated()
        ->assertJsonPath('article.title', 'My First Article')
        ->assertJsonPath('article.slug', 'my-first-article');

    $this->assertDatabaseHas('articles', ['title' => 'My First Article']);
});

test('article creation fails validation for missing fields', function () {
    apiActingAsAuthor(['article.create']);

    $response = $this->postJson('/api/v1/article/create', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'excerpt', 'body', 'category_id', 'status']);
});

test('check article validation test One below Maximum', function () {
    apiActingAsAuthor(['article.create']);

    $article = ['title' => str_repeat('A', 49),
        'category_id' => Category::factory()->create()->id,
        'excerpt' => str_repeat('A', 254),
        'body' => str_repeat('A', 49999),
        'status' => 'draft',
        'duration' => null];

    $this->postJson('/api/v1/article/create', $article)->assertCreated();
});

test('check article validation test One below minimum', function () {
    apiActingAsAuthor(['article.create']);

    $article = ['title' => str_repeat('A', 5),
        'category_id' => 999999,
        'excerpt' => str_repeat('A', 19),
        'body' => str_repeat('A', 29),
        'status' => 'draft',
        'duration' => null];

    $this->postJson('/api/v1/article/create', $article)
        ->assertJsonValidationErrors(['title', 'category_id', 'excerpt', 'body']);
});

test('check article validation test Maximum allowed', function () {
    apiActingAsAuthor(['article.create']);

    $article = ['title' => str_repeat('A', 50),
        'category_id' => Category::factory()->create()->id,
        'excerpt' => str_repeat('A', 255),
        'body' => str_repeat('A', 50000),
        'status' => 'draft',
        'duration' => null];

    $this->postJson('/api/v1/article/create', $article)->assertCreated();
});

test('check article validation test Minimum allowed', function () {
    apiActingAsAuthor(['article.create']);

    $article = ['title' => 'titles',
        'category_id' => Category::factory()->create()->id,
        'excerpt' => str_repeat('A', 20),
        'body' => str_repeat('A', 30),
        'status' => 'draft',
        'duration' => null];

    $this->postJson('/api/v1/article/create', $article)->assertCreated();
});

test('scheduled article creation fails when duration is missing', function () {
    apiActingAsAuthor(['article.create']);

    $response = $this->postJson('/api/v1/article/create', validArticlePayload([
        'status' => ArticleStatus::SCHEDULED->value,
    ]));

    $response->assertStatus(422)->assertJsonValidationErrors(['duration']);
});

test('scheduled article can be created with a valid future duration', function () {
    apiActingAsAuthor(['article.create']);

    $response = $this->postJson('/api/v1/article/create', validArticlePayload([
        'status' => ArticleStatus::SCHEDULED->value,
        'duration' => now()->addHours(2)->toDateTimeString(),
    ]));

    $response->assertCreated();
});

test('article creation generates unique slug for same title', function () {
    $user = apiActingAsAuthor(['article.create']);
    Article::factory()->create(['user_id' => $user->id, 'slug' => 'duplicate-title']);

    $response = $this->postJson('/api/v1/article/create', validArticlePayload(['title' => 'Duplicate Title']));

    $response->assertCreated()->assertJsonPath('article.slug', 'duplicate-title-2');
});

// ----------------------------------------------------------------------
// PUT /api/v1/article/{slug}/update
// ----------------------------------------------------------------------

test('author can update their own article', function () {
    $user = apiActingAsAuthor(['article.edit']);
    $article = Article::factory()->create(['user_id' => $user->id]);

    $response = $this->putJson("/api/v1/article/{$article->slug}/update", validArticlePayload(['title' => 'Updated Article Title']));

    $response->assertOk()->assertJsonPath('article.title', 'Updated Article Title');
});

test('author cannot update another user article', function () {
    apiActingAsAuthor(['article.edit']);
    $article = Article::factory()->create();

    $response = $this->putJson("/api/v1/article/{$article->slug}/update", validArticlePayload());

    $response->assertForbidden();
});

test('admin can update any article', function () {
    apiActingAsAdmin();
    $article = Article::factory()->create();

    $response = $this->putJson("/api/v1/admin/article/{$article->slug}/update", validArticlePayload(['title' => 'Admin Updated Title']));

    $response->assertOk()->assertJsonPath('article.title', 'Admin Updated Title');
});

test('updating a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.edit']);

    $response = $this->putJson('/api/v1/article/does-not-exist/update', validArticlePayload());

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/article/{slug}/delete
// ----------------------------------------------------------------------

test('an author can delete their own article', function () {
    $user = apiActingAsAuthor(['article.delete']);
    $article = Article::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson("/api/v1/article/{$article->slug}/delete");

    $response->assertNoContent();
    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});

test('an author cannot delete another author\'s article', function () {
    apiActingAsAuthor(['article.delete']);
    $article = Article::factory()->create();

    $response = $this->deleteJson("/api/v1/article/{$article->slug}/delete");

    $response->assertForbidden();
});

test('deleting a non-existent article returns a 404', function () {
    apiActingAsAuthor(['article.delete']);

    $response = $this->deleteJson('/api/v1/article/does-not-exist/delete');

    $response->assertNotFound();
});

test('an admin can soft delete an article via the admin delete route', function () {
    apiActingAsAdmin();
    $article = Article::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/article/{$article->slug}/delete");

    $response->assertNoContent();
    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});

// ----------------------------------------------------------------------
// DELETE /api/v1/article/{slug}/forcedelete
// ----------------------------------------------------------------------

test('only superadmin can permanently delete a soft-deleted article', function () {
    $user = apiActingAsAdmin(['article.forceDelete']);
    $article = Article::factory()->create(['user_id' => $user->id]);
    $article->delete();

    $response = $this->deleteJson("/api/v1/admin/article/{$article->slug}/forcedelete");

    $response->assertNoContent();
    $this->assertDatabaseMissing('articles', ['id' => $article->id]);
});

test('can not delete article that is not soft-deleted', function () {
    $user = apiActingAsAdmin(['article.forceDelete']);
    $article = Article::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson("/api/v1/admin/article/{$article->slug}/forcedelete");

    $response->assertStatus(422);
});

test('user without forceDelete permission cannot permanently delete article', function () {
    $user = apiActingAsAuthor([]);
    $article = Article::factory()->create(['user_id' => $user->id]);
    $article->delete();

    $response = $this->deleteJson("/api/v1/admin/article/{$article->slug}/forcedelete");

    $response->assertForbidden();
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/articles
// ----------------------------------------------------------------------

test('an admin can view the admin articles listing', function () {
    apiActingAsAdmin();
    Article::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/articles');

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page']]);
});

test('a non-admin cannot view the admin articles listing', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/admin/articles');

    $response->assertForbidden();
});

test('admin articles listing can be filtered by search term', function () {
    apiActingAsAdmin();
    Article::factory()->create(['title' => 'Laravel Tips and Tricks']);
    Article::factory()->create(['title' => 'Completely Unrelated']);

    $response = $this->getJson('/api/v1/admin/articles?search=Laravel');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

// ----------------------------------------------------------------------
// GET /api/v1/articles
// ----------------------------------------------------------------------

test('author can view the published articles listing', function () {
    apiActingAsAuthor([]);
    Article::factory()->count(2)->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->getJson('/api/v1/articles');

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page']]);
});

test('guest can access articles listing page', function () {
    apiActingAsAdmin();

    $response = $this->getJson('/api/v1/articles');

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page']]);
});

// ----------------------------------------------------------------------
// GET /api/v1/myarticles
// ----------------------------------------------------------------------

test('an author can view their own articles', function () {
    $user = apiActingAsAuthor([]);
    Article::factory()->count(2)->create(['user_id' => $user->id]);
    Article::factory()->create();

    $response = $this->getJson('/api/v1/myarticles');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});
// ----------------------------------------------------------------------
// GET /api/v1/article/{slug}
// ----------------------------------------------------------------------

test('anyone authenticated can view a published article', function () {
    apiActingAsAuthor([]);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    $response = $this->getJson("/api/v1/article/{$article->slug}");

    $response->assertOk()
        ->assertJsonPath('article.slug', $article->slug)
        ->assertJsonStructure(['article', 'is_liked', 'is_bookmarked', 'comments', 'likes_count', 'comments_count']);
});

test('viewing a non-existent article returns a 404', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/article/does-not-exist');

    $response->assertNotFound();
});

test('an admin can view an article via the admin show route', function () {
    apiActingAsAdmin();
    $article = Article::factory()->create();

    $response = $this->getJson("/api/v1/admin/article/{$article->slug}");

    $response->assertOk()->assertJsonPath('article.slug', $article->slug);
});

test('an author cannot view other draft articles', function () {
    apiActingAsAuthor([]);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    $response = $this->getJson("/api/v1/article/{$article->slug}");

    $response->assertNotFound();
});

test('an author can view their own draft articles', function () {
    $user = apiActingAsAuthor([]);
    $article = Article::factory()->create(['user_id' => $user->id, 'status' => ArticleStatus::DRAFT]);

    $response = $this->getJson("/api/v1/article/{$article->slug}");

    $response->assertOk()->assertJsonPath('article.slug', $article->slug);
});

test('author cannot create more than 10 articles per day', function () {
    apiActingAsAuthor();

    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/article/create', validArticlePayload())->assertCreated();
    }

    $this->postJson('/api/v1/article/create', validArticlePayload())->assertStatus(429);
});

test('admin cannot create more than 10 articles per day', function () {
    apiActingAsAdmin();

    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/admin/article/create', validArticlePayload())->assertCreated();
    }

    $this->postJson('/api/v1/admin/article/create', validArticlePayload())->assertStatus(429);
});

test('admin cannot delete more than 50 articles per day', function () {
    apiActingAsAdmin();
    $articles = Article::factory()->count(51)->create();

    foreach ($articles->take(50) as $article) {
        $this->deleteJson("/api/v1/admin/article/{$article->slug}/delete");
    }

    $this->deleteJson("/api/v1/admin/article/{$articles[50]->slug}/delete")->assertStatus(429);
});

test('admin cannot forcedelete more than 50 articles per day', function () {
    apiActingAsAdmin();
    $articles = Article::factory()->count(51)->create();

    foreach ($articles->take(50) as $article) {
        $this->deleteJson("/api/v1/admin/article/{$article->slug}/forcedelete");
    }

    $this->deleteJson("/api/v1/admin/article/{$articles[50]->slug}/forcedelete")->assertStatus(429);
});
