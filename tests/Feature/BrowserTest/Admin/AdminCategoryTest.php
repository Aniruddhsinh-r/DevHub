<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/adminLogin.php';

uses(RefreshDatabase::class);

test('Create category by admin', function () {
    adminLogin();

    visit('/admin/categories')
    ->fill('name','Verdie Littel III')
    ->click('Create Category')
    ->assertRoute('admin.categories')
    ->assertSee('Verdie Littel III');
});

test('delete category', function () {
    adminLogin();

    $category = Category::factory()->create([
        'name' => 'Verdie Littel III'
    ]);

    visit('/admin/categories')
    ->click('[dusk="delete-category-' . $category->id . '"]')
    ->assertDontSee('Verdie Littel III');

    $this->assertDatabaseMissing('categories', [
        'name' => 'Verdie Littel III',
    ]);
});
