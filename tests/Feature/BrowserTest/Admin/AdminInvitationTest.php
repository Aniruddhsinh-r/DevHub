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

test('Admin fetch soft deleted users details', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    AdminLogin();

    visit('/admin/recover-users')
        ->assertSee($user->name);
});

test('User cant visite soft deleted users details page', function () {
    UserLogin();

    visit('/admin/recover-users')
        ->assertSee('403')
        ->assertSee('Forbidden');
});

test('Admin restore soft deleted users.', function () {
    $user = User::factory()->create(['deleted_at'=>	"2026-07-09 12:25:56"]);
    AdminLogin();

    visit('/admin/recover-users')
        ->assertSee($user->name)
        ->click('Restore')
        ->assertSee('User restored successfully.');

    $this->assertDatabaseHas('users', ['id' => $user->id,'deleted_at' => null]);
});

test('Admin send invitation to email.', function () {
    AdminLogin();

    visit('/admin/user/create')
    ->fill('email','testemail@gmail.com')
    ->click('Issue Invitation Link')
    ->assertSee('Invite sent successfully.');

    $this->assertDatabaseHas('invitations', ['email' => 'testemail@gmail.com']);
});

test('Admin resend invitation to email when it expire.', function () {
    $invite = Invitation::factory()->create(['expires_at'=>'2026-07-09 14:20:45']);
    AdminLogin();

    visit('/admin/invitations')
    ->press('Resend')
    ->assertSee('Invitation resent successfully.');

    $this->assertDatabaseHas('invitations', ['email' => $invite->email]);
    $this->assertDatabaseMissing('invitations', ['email' => $invite->email,'expires_at' => $invite->expires_at]);
});