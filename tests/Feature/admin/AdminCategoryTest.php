<?php

use App\Models\User;
use App\Models\Category;

test('Admin category create test', function () {
    $user = User::find(1);

    $response = $this->actingAs($user)->post(route('admin.category.post'), [
        'name' => 'category'
    ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'category'
    ]);
});

test('Admin category delete test', function () {
    $user = User::find(1);
    $category = Category::where('name','category')->value('id');
    $response = $this->actingAs($user)->delete(route('admin.category.delete',$category));

    $this->assertDatabaseMissing('categories', [
        'name' => 'category'
    ]);
});
