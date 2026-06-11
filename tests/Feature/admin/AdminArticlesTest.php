<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('admin search articles', function () {
    $admin = adminLogin();

    Article::factory()->create([
        'title' => 'example article'
    ]);

    $response = $this->actingAs($admin)->get(route('admin.articles', [
        'search' => 'example article'
    ]));

    $response->assertStatus(200);
    $response->assertSee('example article');
});

test('admin view not count', function () {
    $admin = adminLogin();

    $article = Article::factory()->create([
        'title' => 'example article'
    ]);

    $this->actingAs($admin)->get(route('admin.article.show',['article',$article]));
    $this->assertDatabaseMissing('views',['user_id' => $admin->id]);
});
