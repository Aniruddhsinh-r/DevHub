<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/userLogin.php';

// test('it belongs to a user', function () {
//     $idea = Article::factory()->create();
//     expect($idea->user)->toBeInstanceOf(User::class);
// });

test('article create', function () {
    userLogin();

    visit('/articles/create')
    ->fill('title','My testing Amazing Article')
    ->select('category_id','1')
    ->select('status','published')
    ->fill('excerpt','Test case new Amazing Article excerpt testing data')
    ->fill('body','Test case new Article body testing for Amazing article creating and test dummy body data.')
    ->press('[data-test="submitBTN"]')
    ->assertPathIs('/articles');

    // ->assertSee('Article created successfully.');
    // $this->assertDatabaseHas('articles', [
    //     'title' => 'My testing Amazing Article',
    //     'user_id' => $user->id,
    //     'category_id' => $category->id,
    // ]);
    // $response->assertStatus(200);
});
