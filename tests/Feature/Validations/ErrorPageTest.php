<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/UserLogin.php';
require_once __DIR__.'/../Helpers/AdminLogin.php';
require_once __DIR__.'/../Helpers/SuperAdminLogin.php';

beforeEach(function () {
    $this->withoutVite();
});

uses(RefreshDatabase::class);

test('guest hitting a 404 is sent home to the app panel, not admin', function () {
    $response = $this->get('/this-route-does-not-exist');

    $response->assertNotFound();
    $response->assertSee(route('filament.app.pages.home'), false);
    $response->assertDontSee(route('filament.admin.pages.dashboard'), false);
});

test('author hitting a 404 is sent home to the app panel, not admin', function () {
    UserLogin();

    $response = $this->get('/this-route-does-not-exist-xyz123');

    $response->assertNotFound();
    $response->assertSee(route('filament.app.pages.home'), false);
    $response->assertDontSee(route('filament.admin.pages.dashboard'), false);
});

test('admin hitting a 404 is sent to the admin dashboard', function () {
    AdminLogin();

    $response = $this->get('/this-route-does-not-exist-xyz123');

    $response->assertNotFound();
    $response->assertSee(route('filament.admin.pages.dashboard'), false);
    $response->assertDontSee(route('filament.app.pages.home'), false);
});

test('superadmin hitting a 404 is sent to the admin dashboard, not the app panel', function () {
    SuperAdminLogin();

    $response = $this->get('/this-route-does-not-exist-xyz123');

    $response->assertNotFound();
    $response->assertSee(route('filament.admin.pages.dashboard'), false);
    $response->assertDontSee(route('filament.app.pages.home'), false);
});

test('admin hitting a 403 is sent to the admin dashboard', function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    AdminLogin();

    $response = $this->get(route('filament.app.resources.users.view', ['record' => $user]));

    $response->assertForbidden();
    $response->assertSee(route('filament.admin.pages.dashboard'), false);
    $response->assertDontSee(route('filament.app.pages.home'), false);
});

test('superadmin hitting a 403 is sent to the admin dashboard, not the app panel', function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    SuperAdminLogin();

    $response = $this->get(route('filament.app.resources.users.view', ['record' => $user]));

    $response->assertForbidden();
    $response->assertSee(route('filament.admin.pages.dashboard'), false);
    $response->assertDontSee(route('filament.app.pages.home'), false);
});
