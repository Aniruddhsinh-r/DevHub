<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\View;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/adminLogin.php';
require_once __DIR__ . '/../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('Create Article test', function () {
    $user = userLogin();

    Livewire::test('livewirecomponent.article.create-article')
        ->set('title','first testing article.')
        ->set('category_id',Category::factory()->create()->id)
        ->set('excerpt','article created by kishan that gonna delete for purpose.')
        ->set('body','test body content att least 30 character long as i decide test body.')
        ->set('status','published')
        ->call('store')
        ->assertRedirect(route('articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'first testing article.',
        'excerpt' => 'article created by kishan that gonna delete for purpose.',
        'status' => 'published',
    ]);
});

test('user can delete his own article', function () {
    $user = userLogin();
    $article = Article::factory()->create([
        'user_id' => $user->id,
        'category_id' => Category::factory()->create()->id,
    ]);
    Comment::factory()->create(['article_id' => $article->id]);
    Like::factory()->create(['article_id' => $article->id]);
    View::factory()->create(['article_id' => $article->id]);

    Livewire::test('livewirecomponent.article.delete-article',['article' => $article])
        ->call('delete');

    $this->assertSoftDeleted('articles', [
        'id' => $article->id,
        'user_id' => $user->id
    ]);

    $this->assertSoftDeleted('comments', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('likes', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('views', ['article_id' => $article->id]);
    $this->assertDatabaseMissing('bookmarks', ['article_id' => $article->id]);
});

test('user cannot delete others article', function () {
    $user = userLogin();
    $article = Article::factory()->create();

    $response = Livewire::test('livewirecomponent.article.delete-article',['article' => $article]);
    $response->assertStatus(403);

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'deleted_at' => null,
    ]);
});

test('guest cannot access create article page', function () {
    $response = $this->get(route('articles.create'));

    $response->assertRedirect(route('login'));
});

test('admin cannot access create article page', function () {
    $admin = adminLogin();

    $response = $this->actingAs($admin)->get(route('articles.create'));

    $response->assertStatus(403);

    $this->assertDatabaseMissing('articles', [
        'user_id' => $admin->id,
    ]);
});
