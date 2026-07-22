<?php

use App\Models\Category;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';
require_once __DIR__ . '/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('guest is redirected to login when visiting the categories page', function () {
    $this->get('/admin/categories')
        ->assertRedirect('/admin/login');
});

test('admin can render the categories list page', function () {
    AdminLogin();

    Livewire::test(ManageCategories::class)
        ->assertSuccessful();
});

test('admin can see categories in the table', function () {
    AdminLogin();

    $categories = Category::factory()->count(3)->create();

    Livewire::test(ManageCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('admin can search categories by name', function () {
    AdminLogin();

    $category = Category::factory()->create(['name' => 'example category']);
    Category::factory()->create(['name' => 'other category']);

    Livewire::test(ManageCategories::class)
        ->searchTable('example category')
        ->assertCanSeeTableRecords([$category])
        ->assertCanNotSeeTableRecords(Category::where('name', 'other category')->get());
});

test('admin category create test', function () {
    AdminLogin();

    Livewire::test(ManageCategories::class)
        ->callAction('create', data: [
            'name' => 'category',
            'slug' => 'category',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'category',
        'slug' => 'category',
    ]);
});

test('admin category create requires name and slug', function () {
    AdminLogin();

    Livewire::test(ManageCategories::class)
        ->callAction('create', data: [
            'name' => '',
            'slug' => '',
        ])
        ->assertHasActionErrors(['name' => 'required', 'slug' => 'required']);
});

test('admin can update a category', function () {
    AdminLogin();

    $category = Category::factory()->create(['name' => 'old name', 'slug' => 'old-name']);

    Livewire::test(ManageCategories::class)
        ->callTableAction('edit', $category, data: [
            'name' => 'updated name',
            'slug' => 'updated-name',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'updated name',
        'slug' => 'updated-name',
    ]);
});

test('admin category delete test', function () {
    AdminLogin();

    $category = Category::factory()->create(['name' => 'category']);

    Livewire::test(ManageCategories::class)
        ->callTableAction('delete', $category);

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('author cannot access categories page', function () {
    UserLogin();

    $this->get('/admin/categories')
        ->assertForbidden();
});