a<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('Create category by admin', function () {
    AdminLogin();

    visit('/admin/categories')
        ->click('New category')
        ->fill('#mountedActionSchema0\\.name', 'Verdie Littel III')
        ->click('Create')
        ->assertSee('Verdie Littel III');

    $this->assertDatabaseHas('categories', [
        'name' => 'Verdie Littel III',
    ]);
});

test('guest cant access admin category page', function () {
    visit('/admin/categories')
        ->assertPathIs('/admin/login');
});

test('Author cant access admin category page', function () {
    UserLogin();

    visit('/admin/categories')
        ->assertSee('403')
        ->assertSee('Forbidden');
});

test('admin cannot see delete action for a category', function () {
    AdminLogin();

    $category = Category::factory()->create();

    visit('/admin/categories')
        ->assertSee($category->name)
        ->assertDontSee('Delete');
});

test('admin cannot see edit action for a category not owned by them', function () {
    AdminLogin();

    $category = Category::factory()->create();

    visit('/admin/categories')
        ->assertSee($category->name)
        ->assertDontSee('Edit');
});

test('admin can see and use edit action for a category they own', function () {
    $user = AdminLogin();

    $category = Category::factory()->create([
        'name' => 'My Own Category',
        'user_id' => $user->id,
    ]);

    visit('/admin/categories')
        ->assertSee('My Own Category')
        ->click('Edit')
        ->fill('#mountedActionSchema0\\.name', 'Renamed Category')
        ->click('button[type="submit"]:has-text("Save changes")')
        ->assertSee('Renamed Category');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Renamed Category',
    ]);
});

test('admin sees validation error creating category with duplicate name', function () {
    AdminLogin();

    Category::factory()->create(['name' => 'Existing Category', 'slug' => 'existing-category']);

    visit('/admin/categories')
        ->click('New category')
        ->fill('#mountedActionSchema0\\.name', 'Existing Category')
        ->click('Create')
        ->assertSee('The name has already been taken.');

});

test('admin sees required error creating category with empty name', function () {
    AdminLogin();

    visit('/admin/categories')
        ->click('New category')
        ->fill('#mountedActionSchema0\\.name', '')
        ->click('Create')
        ->assertSee('Create Category');
});

test('admin sees min length error when category name is too short', function () {
    AdminLogin();

    visit('/admin/categories')
        ->click('New category')
        ->fill('#mountedActionSchema0\\.name', 'abc')
        ->click('Create')
        ->assertSee('Create Category');
});

test('admin can view category details', function () {
    AdminLogin();

    $category = Category::factory()->create(['name' => 'Viewable Category']);

    visit('/admin/categories')
        ->assertSee('Viewable Category')
        ->click('View')
        ->assertSee('Viewable Category')
        ->assertSee($category->slug);
});

test('admin sees empty state when no categories exist', function () {
    AdminLogin();

    visit('/admin/categories')
        ->assertSee('No categories');
});

test('admin cannot see select all and bulk delete action on category page', function () {
    AdminLogin();

    Category::factory()->count(2)->create();

    visit('/admin/categories')
        ->assertDontSee('Bulk Action');
});
