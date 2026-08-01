<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use App\Enums\UserRole;
require_once __DIR__ . '/../../Helpers/UserLogin.php';
require_once __DIR__ . '/../../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate([
        'name' => UserRole::AUTHOR,
        'guard_name' => 'web'
    ]);
});

test('Register a user', function () {
    $email = 'roman' . time() . '@gmail.com';
 
    visit(route('filament.app.auth.register'))
        ->fill('#form\\.name', 'Romanreigns')
        ->fill('#form\\.email', $email)
        ->fill('#form\\.password', 'Roman123')
        ->fill('#form\\.passwordConfirmation', 'Roman123')
        ->click('button[type="submit"][wire\\:target="register"]');
 
    $this->assertDatabaseHas('users', [
        'email' => $email,
    ]);
});
 
test('after login user cant access register page', function () {
    UserLogin();
 
    visit('/register')
        ->assertRoute('filament.app.pages.home');
});
 
test('after login admin cant access register page', function () {
    AdminLogin();
 
    visit('/register')
        ->assertSee('403');
});