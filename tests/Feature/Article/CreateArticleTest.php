<?php

use App\Models\Article;
use App\Models\User;

test('Create Article test', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('articles.store'), [
        'category_id' => 3,
        'title' => 'first testing article.',
        'status' => 'published',
        'excerpt' => 'Create article using test.',
        'body' => 'test body content att least 30 character long as i decide test body.',
        'published_at' => now(),
    ]);
    $response->dumpSession();

    $response->assertRedirect(route('show.articles'));

    $this->assertDatabaseHas('articles', [
        'title' => 'first testing article.',
        'status' => 'published',
    ]);
});

test('user can delete own article', function () {
    $user = User::find(22);
    // $article = Article::find(36);
    $article = Article::factory()->create([
        'user_id' => 22,
        'category_id' => 3,
    ]);

    $response = $this->actingAs($user)->delete(route('articles.destroy',$article->id));

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
    ]);

    $this->assertDatabaseMissing('comments', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('views', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('user cannot delete others article', function () {
    $user = User::find(22);
    $article = Article::find(4);

    $response = $this->actingAs($user)->delete(route('articles.destroy', $article->id));
    $response->assertStatus(403);

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'deleted_at' => null,
    ]);
});

test('guest cannot create article', function () {
    $response = $this->post(route('articles.store'), [
        'category_id' => 3,
        'title' => 'gguest article.',
        'status' => 'published',
        'excerpt' => 'Create article using test.',
        'body' => 'test body content att least 30 character long as i decide test body.',
        'published_at' => now(),
    ]);

    $response->assertRedirect(route('login'));
    // $response->assertRedirect('/articles');

    $this->assertDatabaseMissing('articles', [
        'title' => 'gguest article.',
        'excerpt' => 'Create article using test.',
    ]);
});

test('admin cannot post article', function () {
    $admin = User::find(1);

    $response = $this->actingAs($admin)->post(route('articles.store'), [
        'category_id' => 3,
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
