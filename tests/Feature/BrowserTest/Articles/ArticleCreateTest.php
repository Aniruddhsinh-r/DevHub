<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
require_once __DIR__ . '/../Helpers/UserLogin.php';

test('it belongs to a user', function () {
    $idea = Article::factory()->create([
        'category_id' => 5
    ]);
    expect($idea->user)->toBeInstanceOf(User::class);
});

test('article create', function () {
    userLogin();

    visit('/articles/create')
    ->fill('title','hither loggy')
    ->select('category_id','1')
    ->select('status','published')
    ->fill('excerpt','this is test case for checking hope this work.')
    ->fill('body','Test case new Article body testing for Amazing article creating and test dummy body data.')
    ->press('@submitBTN')
    ->assertRoute('show.articles');
    // ->assertRoute(route('show.articles'));

    $this->assertDatabaseHas('articles', [
        'title' => 'hither loggy',
        'status' => 'published',
        'excerpt' => 'this is test case for checking hope this work.',
    ]);
});
