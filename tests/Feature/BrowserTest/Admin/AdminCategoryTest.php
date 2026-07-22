<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('Create category by admin', function () {
    AdminLogin();
 
    visit('/admin/categories')
        ->click('New category')
        ->fill('#mountedActionSchema0\\.name', 'Verdie Littel III')
        ->fill('#mountedActionSchema0\\.slug', 'verdie-littel-iii')
        ->click('Create')
        ->assertSee('Verdie Littel III');
 
    $this->assertDatabaseHas('categories', [
        'name' => 'Verdie Littel III',
    ]);
});
 
test('delete category', function () {
    AdminLogin();
 
    $category = Category::factory()->create();
 
    visit('/admin/categories')
        ->assertSee($category->name)
        ->click('Delete')
        ->click('button[wire\:target="callMountedAction"]')
        ->assertDontSee($category->name);
 
    $this->assertDatabaseMissing('categories', [
        'name' => $category->name,
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