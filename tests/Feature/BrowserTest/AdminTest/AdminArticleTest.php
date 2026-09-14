<?php

use App\Enums\UserRole;
use App\Filament\Widgets\TopAuthors;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Like;
use App\Models\User;
use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
});

test('Admin fetch Article details', function () {
    $article = Article::factory()->create(['title' => 'new article']);
    AdminLogin();

    visit('/admin/articles')
        ->assertSee('new article');
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

test('admin can create an article and slug collisions are handled', function () {
    AdminLogin();

    Article::factory()->create(['title' => 'My Test Title', 'slug' => 'my-test-title']);
    $category = Category::factory()->create();

    visit('/admin/articles/create')
        ->fill('#form\.title', 'My Test Title')
        ->click('.fi-select-input-btn:has-text("Select an option")')
        ->click($category->name)
        ->select('#form\.status', 'published')
        ->fill('#form\.excerpt', 'Excerpt for collision test.')
        ->fill('#form\.body', 'Body content for the admin-created article collision test.')
        ->click('#key-bindings-1')
        ->assertUrlIs(route('filament.admin.resources.articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'My Test Title',
        'slug' => 'my-test-title-2',
        'status' => 'published',
    ]);
});

test('admin dashboard renders stats and widgets with correct data', function () {
    AdminLogin();

    $topAuthor = User::factory()->create(['name' => 'Prolific Author']);
    $topAuthor->assignRole(UserRole::AUTHOR);
    Article::factory()->count(3)->create(['user_id' => $topAuthor->id]);

    $latest = Article::factory()->create(['title' => 'Freshly Published Article']);

    visit('/admin')
        ->assertSee('System Performance')
        ->assertSee('Total Articles')
        ->assertSee('Likes')
        ->assertSee('Freshly Published Article')
        ->assertSee('Prolific Author');
});

test('likes tab on admin article view shows who liked the article', function () {
    AdminLogin();

    $article = Article::factory()->create();
    $liker = User::factory()->create(['name' => 'Someone Who Liked It']);
    Like::factory()->create(['article_id' => $article->id, 'user_id' => $liker->id]);

    visit('/admin/articles/'.$article->slug)
        ->click('Likes')
        ->assertSee('Someone Who Liked It');
});

test('top authors widget returns correct stats', function () {
    AdminLogin();

    $topAuthor = User::factory()->create(['name' => 'Top Test Author',]);

    $otherAuthor = User::factory()->create(['name' => 'Other Test Author',]);

    // Top author must be selected by article count.
    Article::factory()->count(3)->create(['user_id' => $topAuthor->id,]);

    Article::factory()->create(['user_id' => $otherAuthor->id,]);

    $article = Article::factory()->create();

    // Total comments = 8
    Comment::factory()->count(8)->create(['article_id' => $article->id,]);

    // Total likes = 3
    $likeUsers = User::factory()->count(3)->create();

    foreach ($likeUsers as $user) {
        Like::factory()->create(['article_id' => $article->id,'user_id' => $user->id,]);
    }

    // Deliberately different from likes.
    Bookmark::factory()->count(20)->create(['article_id' => $article->id,]);

    $method = new ReflectionMethod(TopAuthors::class, 'getStats');
    $method->setAccessible(true);

    $widget = app(TopAuthors::class);
    $stats = $method->invoke($widget);

    expect($stats)->toHaveCount(3)
        ->and($stats[0])->toBeInstanceOf(Stat::class)
        ->and($stats[0]->getLabel())->toBe('Top Author')
        ->and($stats[0]->getValue())->toBe('Top Test Author')
        ->and($stats[1]->getLabel())->toBe('Total Comments')
        ->and($stats[1]->getValue())->toBe(8)
        ->and($stats[2]->getLabel())->toBe('Total Likes')
        ->and($stats[2]->getValue())->toBe(3);
});
