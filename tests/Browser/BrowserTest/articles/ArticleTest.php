<?php

use App\Models\Article;
use App\Models\User;

require_once __DIR__.'/../../Helpers/userLogin.php';

// test('visite specific articles', function () {
//     userLogin();

//     $article = Article::factory()->create([
//         'title' => 'example Article9',
//     ]);

//     // visit('/articles')
//     // ->assertSee('example Article7');

//     visit('/articles?search=example Article9')
//     ->click('example Article9')
//     ->assertPathIs('/articles/' . $article->id);

//     expect($article->fresh()->view_count)->toBe(1);
// });

test('functionality check in articles.',function () {
    userLogin();

    $user_id = User::where('email','adanirudda@gmail.com')->value('id');
    $article_id = 29;

    visit('/articles/29')
        ->click('@like-button')
        ->click('@bookmark-button')
        ->fill('body','Hi there this is my first comment.')
        ->press('@PostComment')
        // ->assertSee('Hi there this is my first comment.')
        ;

        $this->assertDatabaseHas('likes',['article_id'=>$article_id , 'user_id'=>$user_id]);
        $this->assertDatabaseHas('bookmarks',['article_id'=>$article_id , 'user_id'=>$user_id]);

});
