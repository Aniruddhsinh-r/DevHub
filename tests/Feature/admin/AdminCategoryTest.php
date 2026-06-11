<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('Admin category create test', function () {
    $admin = AdminLogin();

    $this->actingAs($admin)->post(route('admin.category.post'), [
        'name' => 'category'
    ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'category'
    ]);
});

test('Admin category delete test', function () {
    $admin = AdminLogin();
    Category::factory()->create([
        'name' => 'category'
    ]);

    $category = Category::where('name','category')->value('id');
    $this->actingAs($admin)->delete(route('admin.category.delete',$category));

    $this->assertDatabaseMissing('categories', [
        'name' => 'category'
    ]);
});
