<?php

use App\Models\Category;

require_once __DIR__.'/../Helpers/adminLogin.php';

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

    $category = Category::where('name', 'Verdie Littel III')->first();

    visit('/admin/categories')
    ->click('[dusk="delete-category-' . $category->id . '"]')
    ->assertDontSee('Verdie Littel III');
});
