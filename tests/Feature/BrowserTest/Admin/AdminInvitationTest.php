<?php

use App\Models\User;
use App\Models\Invitation;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
});

test('Admin send invitation to email.', function () {
    AdminLogin();
 
    visit('/admin/invitations')
        ->click('New invitation')
        ->fill('#mountedActionSchema0\\.email', 'testemail@gmail.com')
        ->click('Create')
        ->assertSee('testemail@gmail.com');
 
    $this->assertDatabaseHas('invitations', ['email' => 'testemail@gmail.com']);
});
 
test('Admin cant send duplicate invitation for an active user', function () {
    $user = User::factory()->create(['email' => 'testemail@gmail.com']);
    AdminLogin();
 
    visit('/admin/invitations')
        ->click('New invitation')
        ->fill('#mountedActionSchema0\\.email', 'testemail@gmail.com')
        ->click('Create')
        ->assertSee('This email is already registered to an active account.');
});
 
test('Admin resend invitation to email when it expire.', function () {
    $invite = Invitation::factory()->create([
        'status' => 'expired',
        'expires_at' => now()->subMinutes(5),
    ]);
    AdminLogin();
 
    visit('/admin/invitations')
        ->click('Resend')
        ->assertSee('Invitation resent successfully.');
 
    $this->assertDatabaseHas('invitations', ['email' => $invite->email, 'status' => 'pending']);
});
 
test('guest cant access admin invitations page', function () {
    visit('/admin/invitations')
        ->assertPathIs('/admin/login');
});
 
test('Author cant access admin invitations page', function () {
    UserLogin();
 
    visit('/admin/invitations')
        ->assertSee('403')
        ->assertSee('Forbidden');
});