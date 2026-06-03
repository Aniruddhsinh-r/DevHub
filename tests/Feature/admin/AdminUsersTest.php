<?php

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;

test('Admin User find test', function () {
    $user = User::find(1);

    $response = $this->actingAs($user)->get(route('admin.users', [
        'search' => 'Rathod Aniruddhsinh Jayeshbhai'
    ]));

    $response->assertStatus(200);

    $response->assertSee('Rathod Aniruddhsinh Jayeshbhai');
});

test('Admin User delete test', function () {
    $this->withoutExceptionHandling();
    $user = User::find(1);
    $removeuser = User::factory()->create();

    Article::factory()->create(['user_id' => $removeuser->id, 'category_id' =>2]);
    Comment::factory()->create(['user_id' => $removeuser->id, 'article_id'=> 4]);
    Like::factory()->create(['user_id' => $removeuser->id]);

    $response = $this->actingAs($user)->delete(route('admin.user.remove',$removeuser->id));

    $this->assertSoftDeleted('users',['id' => $removeuser->id]);
    $this->assertSoftDeleted('articles',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('bookmarks',['user_id' => $removeuser->id]);
    $this->assertSoftDeleted('comments',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('likes',['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('views',['user_id' => $removeuser->id]);
});

