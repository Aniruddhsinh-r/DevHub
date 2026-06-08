<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Admin category create test', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($user)->post(route('admin.category.post'), [
        'name' => 'category'
    ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'category'
    ]);
});

test('Admin category delete test', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);
    Category::factory()->create([
        'name' => 'category'
    ]);

    $category = Category::where('name','category')->value('id');
    $this->actingAs($user)->delete(route('admin.category.delete',$category));

    $this->assertDatabaseMissing('categories', [
        'name' => 'category'
    ]);
});
