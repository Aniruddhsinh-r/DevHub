<?php

use App\Models\User;
use App\Models\Article;
use Livewire\Livewire;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});
test('Admin User find test', function () {
    $admin = AdminLogin();

    User::factory()->create([
        'name' => 'ishigory'
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users', [
        'search' => 'ishigory'
    ]));

    $response->assertStatus(200);
    $response->assertSee('ishigory');
});

test('Admin User delete test', function () {
    AdminLogin();

    $removeuser = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $removeuser->id,]);
    Comment::factory()->create(['user_id' => $removeuser->id,'article_id' => $article->id]);

    Article::factory()->create(['user_id' => $removeuser->id, ]);
    Comment::factory()->create(['user_id' => $removeuser->id, 'article_id'=> $article->id]);
    Like::factory()->create(['user_id' => $removeuser->id]);

    Livewire::test('livewirecomponent.admin.users')
    ->call('remove', $removeuser->id);

    $this->assertSoftDeleted('users',['id' => $removeuser->id]);
    $this->assertSoftDeleted('articles',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('bookmarks',['user_id' => $removeuser->id]);
    $this->assertSoftDeleted('comments',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('likes',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('views',['user_id' => $removeuser->id]);
});

