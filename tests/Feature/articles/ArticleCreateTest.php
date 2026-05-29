<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// test('it belongs to a user', function () {
//     $idea = Article::factory()->create();
//     expect($idea->user)->toBeInstanceOf(User::class);
// });

test('example', function () {

    visit('/login')
    ->fill('email', 'adanirudda@gmail.com')
    ->fill('password', '1290')
    ->press('@login-btn');

    visit('/articles/create')
    ->fill('title','Testcase Article')
    ->select('category_id','Est')
    ->select('status','published')
    ->fill('excerpt','TestcaseArticle excerpt testing')
    ->fill('body','TestcaseArticle body testing for create article test dummy body.')
    ->press('@submitBTN')
    // ->assertRoute('show.articles');
    ->assertSee('Article created successfully.');
    // $this->assertDatabaseHas('articles', [
    //     'title' => 'My First Amazing Article',
    //     'user_id' => $user->id,
    //     'category_id' => $category->id,
    // ]);
    // $response->assertStatus(200);
});
