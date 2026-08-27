<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

test('a guest cannot create a category', function () {
    $response = $this->postJson('/api/v1/admin/category/create', ['name' => 'Technology']);

    $response->assertStatus(401);
});

test('admin can create a category', function () {
    apiActingAsAdmin(['category.create']);

    $response = $this->postJson('/api/v1/admin/category/create', ['name' => 'Technology']);

    $response->assertCreated()
        ->assertJsonPath('category.name', 'Technology')
        ->assertJsonPath('category.slug', 'technology');

    $this->assertDatabaseHas('categories', ['name' => 'Technology', 'slug' => 'technology']);
});

test('a user without permission cannot create a category', function () {
    apiActingAsAuthor([]);

    $response = $this->postJson('/api/v1/admin/category/create', ['name' => 'Technology']);

    $response->assertForbidden();
});

test('category validation fails when name length is short', function () {
    apiActingAsAdmin(['category.create']);

    $response = $this->postJson('/api/v1/admin/category/create', ['name' => 'ab']);

    $response->assertStatus(422)->assertJsonValidationErrors(['name']);
});

test('category creation fails when the name is already taken', function () {
    apiActingAsAdmin(['category.create']);
    Category::factory()->create(['name' => 'Technology']);

    $response = $this->postJson('/api/v1/admin/category/create', ['name' => 'Technology']);

    $response->assertStatus(422);
});

test('admin can see list of categories', function () {
    apiActingAsAdmin(['category.list']);
    Category::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/categories');

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page'],]);
});

test('author cannot list categories', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/admin/categories');

    $response->assertForbidden();
});

test('admin can update their owned category', function () {
    $admin = apiActingAsAdmin(['category.edit']);
    $category = Category::factory()->create(['user_id' => $admin->id]);

    $response = $this->putJson("/api/v1/admin/category/{$category->id}/update", ['name' => 'UpdatedName']);

    $response->assertOk()->assertJsonPath('category.name', 'UpdatedName');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'UpdatedName']);
});

test('admin cannot update a category they do not own', function () {
    apiActingAsAdmin(['category.edit']);
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/admin/category/{$category->id}/update", ['name' => 'UpdatedName']);

    $response->assertForbidden();
});

test('superadmin can update any category', function () {
    apiActingAsSuperAdmin();
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/admin/category/{$category->id}/update", ['name' => 'SuperUpdated']);

    $response->assertOk()->assertJsonPath('category.name', 'SuperUpdated');
});

test('category update fails validation', function () {
    $admin = apiActingAsAdmin(['category.edit']);
    $category = Category::factory()->create(['user_id' => $admin->id]);

    $response = $this->putJson("/api/v1/admin/category/{$category->id}/update", ['name' => '']);

    $response->assertStatus(422)->assertJsonValidationErrors(['name']);
});

test('superadmin can delete a category', function () {
    apiActingAsAdmin(['category.delete']);
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/category/{$category->id}/delete");

    $response->assertNoContent();
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('user without delete permission cannot delete a category', function () {
    apiActingAsAdmin([]);
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/category/{$category->id}/delete");

    $response->assertForbidden();
});

test('admin can view a single category', function () {
    apiActingAsAdmin(['category.list', 'user.manage']);
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/admin/category/{$category->id}");

    $response->assertOk()->assertJsonPath('id', $category->id);
});

test('author cannot view a single category', function () {
    apiActingAsAuthor([]);
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/admin/category/{$category->id}");

    $response->assertForbidden();
});

test('viewing a non-existent category returns a 404', function () {
    apiActingAsAdmin(['category.list']);

    $response = $this->getJson('/api/v1/admin/category/999999');

    $response->assertNotFound();
});
