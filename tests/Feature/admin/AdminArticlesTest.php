<?php

use App\Models\User;

test('admin search articles', function () {
    $user = User::find(1);

    $response = $this->actingAs($user)->get(route('admin.articles', [
        'search' => 'example article'
    ]));

    $response->assertStatus(200);
    $response->assertSee('example article');

    $this->actingAs($user)->get(route('admin.article.show',21));
    $this->assertDatabaseMissing('views',['user_id' => $user->id]);
});
