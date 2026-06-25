<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/adminLogin.php';
require_once __DIR__.'/../../Helpers/userLogin.php';

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

    $page = visit('/admin/categories');
    $page->script('window.confirm = () => true;');
    $page->click('[dusk="delete-category-' . $category->id . '"]')
    ->assertDontSee('Verdie Littel III');

    $this->assertDatabaseMissing('categories', [
        'name' => 'Verdie Littel III',
    ]);
});

test('guest cant access admin category page', function () {
    visit('/admin/categories')
    ->assertRoute('login');
});

test('Author cant access admin category page', function () {
    userLogin();

    visit('/admin/categories')
    ->assertSee('403')
    ->assertSee('Forbidden');
});
