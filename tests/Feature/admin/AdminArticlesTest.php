<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin search articles', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    Article::factory()->create([
        'title' => 'example article'
    ]);

    $response = $this->actingAs($user)->get(route('admin.articles', [
        'search' => 'example article'
    ]));

    $response->assertStatus(200);
    $response->assertSee('example article');

    $this->actingAs($user)->get(route('admin.article.show',21));
    $this->assertDatabaseMissing('views',['user_id' => $user->id]);
});
