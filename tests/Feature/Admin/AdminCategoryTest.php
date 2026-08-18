<?php

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/AdminLogin.php';
require_once __DIR__.'/../Helpers/SuperAdminLogin.php';
require_once __DIR__.'/../Helpers/UserLogin.php';

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
        ])
        ->assertHasActionErrors(['name' => 'required']);
});

test('admin can update a category', function () {
    AdminLogin();

    $category = Category::factory()->create(['name' => 'old name', 'slug' => 'old-name', 'user_id' => auth()->id()]);

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
    SuperAdminLogin();

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

test('prevents categories with the same normalized slug', function () {
    AdminLogin();

    Category::factory()->create([
        'name' => 'Web Dev',
        'slug' => 'web-dev',
    ]);

    Livewire::test(ManageCategories::class)
        ->callAction('create', data: [
            'name' => 'Web-Dev',
        ])
        ->assertHasFormErrors(['name']);
});

test('admin cannot see edit action for a category not owned by them', function () {
    AdminLogin();

    $category = Category::factory()->create();

    expect(auth()->user()->can('update', $category))->toBeFalse();

    Livewire::test(ManageCategories::class)
        ->assertTableActionHidden('edit', $category);
});

test('admin cannot see delete action on a category', function () {
    AdminLogin();

    $category = Category::factory()->create();

    expect(auth()->user()->can('delete', $category))->toBeFalse();

    Livewire::test(ManageCategories::class)
        ->assertTableActionHidden('delete', $category);
});

test('search input is safe against SQL-injection-style strings', function () {
    AdminLogin();

    Category::factory()->create(['name' => 'safe category']);

    Livewire::test(ManageCategories::class)
        ->searchTable("' OR '1'='1")
        ->assertCanNotSeeTableRecords(Category::all());
});

test('hidden user_id field cannot be used to reassign category ownership during edit', function () {
    AdminLogin();

    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'name' => 'mine',
        'slug' => 'mine',
    ]);
    $otherUser = User::factory()->create();

    Livewire::test(ManageCategories::class)
        ->callTableAction('edit', $category, data: ['name' => 'mine','user_id' => $otherUser->id,]);

    expect($category->fresh()->user_id)->toBe(auth()->id());
});
