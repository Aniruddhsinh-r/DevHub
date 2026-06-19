<?php

use App\Models\Category;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__ . '/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('Admin category create test', function () {
    $admin = AdminLogin();

    Livewire::test('livewirecomponent.admin.category-list')
    ->set('name','category')
    ->call('create')
    ->assertDispatched('live-notification', message: 'Category created successfully.');

    $this->assertDatabaseHas('categories', [
        'name' => 'category'
    ]);
});

test('Admin category delete test', function () {
    $admin = AdminLogin();
    Category::factory()->create([
        'name' => 'category'
    ]);

    $category = Category::factory()->create();
    Livewire::test('livewirecomponent.admin.category-list',['category' => $category]);

    $this->assertDatabaseMissing('categories', [
        'name' => $category->id
    ]);
});
