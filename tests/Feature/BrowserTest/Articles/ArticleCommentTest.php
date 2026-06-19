<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';

uses(RefreshDatabase::class);

test('Author comment on article', function () {
    UserLogin();

    $article = Article::factory()->create();
    visit(route('articles.show', $article))
    ->fill('body',"hello this comment created by browser comment testing.")
    ->press('Comment')
    ->assertSee('Comment successfully posted.');

    $this->assertDatabaseHas('comments', [
        'user_id' => auth()->id(),
        'article_id' => $article->id,
        'body' => 'hello this comment created by browser comment testing.',
    ]);
});
