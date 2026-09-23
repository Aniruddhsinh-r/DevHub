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

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page']]);
});

test('admin cannot fetch user category page', function () {
    apiActingAsAdmin([]);

    $response = $this->getJson('/api/v1/categories');

    $response->assertForbidden();
});

test('author can only see id and category name', function () {
    apiActingAsAuthor([]);

    Category::factory()->create(['name' => 'Technology']);

    $response = $this->getJson('/api/v1/categories');
    $firstItem = $response->json('0');

    expect(array_keys($firstItem))->toEqualCanonicalizing(['id', 'name']);
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

test('admin cannot create more than 4 categories per day', function () {
    apiActingAsAdmin();

    for ($i = 0; $i < 4; $i++) {
        $this->postJson('/api/v1/admin/category/create', ['name' => 'Category '.$i])->assertStatus(201);
    }

    $this->postJson('/api/v1/admin/category/create', ['name' => 'One too many'])->assertStatus(429);
});

test('admin cannot update more than 4 categories per day', function () {
    apiActingAsSuperAdmin();
    $categories = Category::factory()->count(5)->create();

    foreach ($categories->take(4) as $i => $category) {
        $this->putJson("/api/v1/admin/category/{$category->id}/update", ['name' => 'Updated '.$i])->assertStatus(200);
    }

    $this->putJson("/api/v1/admin/category/{$categories[4]->id}/update", ['name' => 'One too many'])->assertStatus(429);
});

test('admin cannot delete more than 4 categories per day', function () {
    apiActingAsAdmin(['category.delete']);
    $categories = Category::factory()->count(5)->create();

    foreach ($categories->take(4) as $category) {
        $this->deleteJson("/api/v1/admin/category/{$category->id}/delete")->assertStatus(204);
    }

    $this->deleteJson("/api/v1/admin/category/{$categories[4]->id}/delete")->assertStatus(429);
});

test('limits category pagination to 100 records', function () {
    apiActingAsAdmin();
    Category::factory()->count(105)->create();

    $this->getJson('/api/v1/admin/categories?per_page=1')->assertOk()->assertJsonPath('meta.per_page', 1);
    $this->getJson('/api/v1/admin/categories?per_page=93')->assertOk()->assertJsonPath('meta.per_page', 93);
    $this->getJson('/api/v1/admin/categories?per_page=100')->assertOk()->assertJsonPath('meta.per_page', 100);
    $this->getJson('/api/v1/admin/categories?per_page=101')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    $this->getJson('/api/v1/admin/categories?per_page=abc')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    $this->getJson('/api/v1/admin/categories?per_page=25abc')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    $this->getJson('/api/v1/admin/categories?per_page=-1')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    $this->getJson('/api/v1/admin/categories?per_page=0')->assertStatus(422)->assertJsonValidationErrors(['per_page']);
});
