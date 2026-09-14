<?php

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
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

test('guest completes invitation registration end to end', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'browserinvitee@example.com',
        'token' => Str::random(32),
    ]);

    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => $invitation->token]);

    visit($url)
        ->fill('#content\\.name', 'Browser Invitee')
        ->fill('#content\\.password', 'password123')
        ->click('Create account')
        ->assertUrlIs(route('filament.app.pages.home'));

    $this->assertDatabaseHas('users', ['email' => 'browserinvitee@example.com']);
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'status' => 'accepted']);
});

test('Admin cannot resend an invitation that is still pending and not expired', function () {
    $invite = Invitation::factory()->create(['status' => 'pending', 'expires_at' => now()->addMinutes(20)]);
    AdminLogin();

    visit('/admin/invitations')
        ->click('Resend')
        ->assertSee('Please wait');

    $this->assertDatabaseHas('invitations', ['email' => $invite->email, 'status' => 'pending']);
});

test('Admin sending an invitation to a blocked (soft-deleted) email is rejected', function () {
    $trashed = User::factory()->create(['email' => 'blocked@example.com']);
    $trashed->delete();
    AdminLogin();

    visit('/admin/invitations')
        ->click('New invitation')
        ->fill('#mountedActionSchema0\\.email', 'blocked@example.com')
        ->click('Create')
        ->assertSee('This email is blocked.');

    $this->assertDatabaseMissing('invitations', ['email' => 'blocked@example.com']);
});
