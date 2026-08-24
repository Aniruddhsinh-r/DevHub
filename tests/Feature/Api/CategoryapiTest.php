<?php

use App\Enums\UserRole;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

test('a guest cannot create a category', function () {
    $response = $this->postJson('/api/v1/category/create', ['name' => 'Technology']);

    $response->assertStatus(401);
});

test('admin can create a category', function () {
    apiActingAsAuthor(['category.create']);

    $response = $this->postJson('/api/v1/category/create', ['name' => 'Technology']);

    $response->assertCreated()
        ->assertJsonPath('category.name', 'Technology')
        ->assertJsonPath('category.slug', 'technology');

    $this->assertDatabaseHas('categories', ['name' => 'Technology', 'slug' => 'technology']);
});

test('a user without permission cannot create a category', function () {
    apiActingAsAuthor([]);

    $response = $this->postJson('/api/v1/category/create', ['name' => 'Technology']);

    $response->assertForbidden();
});

test('category validation fails when name length is short', function () {
    apiActingAsAuthor(['category.create']);

    $response = $this->postJson('/api/v1/category/create', ['name' => 'ab']);

    $response->assertStatus(422)->assertJsonValidationErrors(['name']);
});

test('category creation fails when the name is already taken', function () {
    apiActingAsAuthor(['category.create']);
    Category::factory()->create(['name' => 'Technology']);

    $response = $this->postJson('/api/v1/category/create', ['name' => 'Technology']);

    $response->assertStatus(422);
});

test('admin can see list of categories', function () {
    apiActingAsAuthor(['category.list']);
    Category::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()->assertJsonStructure(['data', 'current_page']);
});

test('author cannot list categories', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/categories');

    $response->assertForbidden();
});

test('admin can update their owned category', function () {
    $admin = apiActingAsAdmin(['category.edit']);
    $category = Category::factory()->create(['user_id' => $admin->id]);

    $response = $this->putJson("/api/v1/category/{$category->id}/update", ['name' => 'UpdatedName']);

    $response->assertOk()->assertJsonPath('category.name', 'UpdatedName');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'UpdatedName']);
});

test('admin cannot update a category they do not own', function () {
    apiActingAsAuthor(['category.edit']);
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/category/{$category->id}/update", ['name' => 'UpdatedName']);

    $response->assertForbidden();
});

test('superadmin can update any category', function () {
    apiActingAsSuperAdmin();
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/category/{$category->id}/update", ['name' => 'SuperUpdated']);

    $response->assertOk()->assertJsonPath('category.name', 'SuperUpdated');
});

test('category update fails validation', function () {
    $admin = apiActingAsAdmin(['category.edit']);
    $category = Category::factory()->create(['user_id' => $admin->id]);

    $response = $this->putJson("/api/v1/category/{$category->id}/update", ['name' => '']);

    $response->assertStatus(422)->assertJsonValidationErrors(['name']);
});

test('superadmin can delete a category', function () {
    apiActingAsAdmin(['category.delete']);
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/category/{$category->id}/delete");

    $response->assertOk()->assertJson(['message' => 'category deleted successfully.']);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('user without delete permission cannot delete a category', function () {
    apiActingAsAuthor([]);
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/category/{$category->id}/delete");

    $response->assertForbidden();
});

test('admin can view a single category', function () {
    apiActingAsAdmin(['category.list', 'user.manage']);
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/category/{$category->id}");

    $response->assertOk()->assertJsonPath('id', $category->id);
});

test('author cannot view a single category', function () {
    apiActingAsAuthor([]);
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/category/{$category->id}");

    $response->assertForbidden();
});

test('viewing a non-existent category returns a 404', function () {
    apiActingAsAdmin(['category.list']);

    $response = $this->getJson('/api/v1/category/999999');

    $response->assertNotFound();
});