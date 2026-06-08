<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Create Article test', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('articles.store'), [
        'category_id' => Category::factory()->create()->id,
        'title' => 'first testing article.',
        'status' => 'published',
        'excerpt' => 'Create article using test.',
        'body' => 'test body content att least 30 character long as i decide test body.',
        'published_at' => now(),
    ]);

    $response->assertRedirect(route('articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'first testing article.',
        'status' => 'published',
    ]);
});

test('user can delete own article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create([
        'user_id' => $user->id,
        'category_id' => Category::factory()->create(),
    ]);

    $this->actingAs($user)->delete(route('articles.destroy',$article->id));

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
    ]);

    $this->assertDatabaseMissing('comments', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('views', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('user cannot delete others article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    $response = $this->actingAs($user)->delete(route('articles.destroy', $article->id));
    $response->assertStatus(403);

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'deleted_at' => null,
    ]);
});

test('guest cannot create article', function () {
    $response = $this->post(route('articles.store'), [
        'category_id' => Category::factory()->create(),
        'title' => 'gguest article.',
        'status' => 'published',
        'excerpt' => 'Create article using test.',
        'body' => 'test body content att least 30 character long as i decide test body.',
        'published_at' => now(),
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseMissing('articles', [
        'title' => 'gguest article.',
        'excerpt' => 'Create article using test.',
    ]);
});

test('admin cannot post article', function () {
    $admin = User::factory()->create([
        'role' => 'admin'
    ]);

    $response = $this->actingAs($admin)->post(route('articles.store'), [
        'category_id' => Category::factory()->create(),
        'title' => 'first testing article.',
        'status' => 'published',
        'excerpt' => 'Create article using test.',
        'body' => 'test body content att least 30 character long as i decide test body.',
        'published_at' => now(),
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('articles', [
        'user_id' => $admin->id,
    ]);
});
