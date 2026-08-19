<?php

use App\Enums\UserRole;
use App\Filament\App\Pages\InvitationRegister;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
});

test('valid invitation link pre-fills the email and renders the form', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'invitee@example.com',
        'token' => Str::random(32),
    ]);

    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => $invitation->token]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee('invitee@example.com');
});

test('completing invitation registration creates the user, assigns author role, and marks the invitation accepted', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'newauthor@example.com',
        'token' => Str::random(32),
    ]);

    Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
        ->set('data.name', 'New Author')
        ->set('data.password', 'password123')
        ->call('register');

    $user = User::where('email', 'newauthor@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole(UserRole::AUTHOR))->toBeTrue();
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'status' => 'accepted']);
});

test('nonexistent invitation token returns 410', function () {
    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => 'does-not-exist']);

    $this->get($url)->assertStatus(410);
});

test('already accepted invitation returns 409', function () {
    $invitation = Invitation::factory()->create(['token' => Str::random(32), 'status' => 'accepted']);
    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => $invitation->token]);

    $this->get($url)->assertStatus(409);
});

test('expired invitation returns 410', function () {
    $invitation = Invitation::factory()->create(['token' => Str::random(32), 'expires_at' => now()->subMinute()]);
    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => $invitation->token]);

    $this->get($url)->assertStatus(410);
});

test('invitation link with a tampered signature is rejected', function () {
    $invitation = Invitation::factory()->create(['token' => Str::random(32)]);

    $this->get('/invitation/'.$invitation->token.'?expires=9999999999&signature=tampered')
        ->assertForbidden();
});

test('authenticated user visiting an invitation link is redirected without consuming it', function () {
    UserLogin();

    $invitation = Invitation::factory()->create(['token' => Str::random(32)]);
    $url = URL::temporarySignedRoute('invitation-register', now()->addMinutes(30), ['token' => $invitation->token]);

    $this->get($url)->assertRedirect('/home');

    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'status' => 'pending']);
});

test('tampering the disabled email field rejects registration with a warning', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'real-invitee@example.com',
        'token' => Str::random(32),
    ]);

    Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
        ->set('data.email', 'attacker@example.com')
        ->set('data.name', 'Attacker')
        ->set('data.password', 'password123')
        ->call('register')
        ->assertNotified('This Invitation email is not valid.');

    $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id, 'status' => 'pending']);
});

test('registration is rejected if the invited email was registered elsewhere first', function () {
    $invitation = Invitation::factory()->create([
        'email' => 'racecondition@example.com',
        'token' => Str::random(32),
    ]);
    User::factory()->create(['email' => 'racecondition@example.com']);

    Livewire::test(InvitationRegister::class, ['token' => $invitation->token])
        ->set('data.name', 'Too Late')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->call('register')
        ->assertStatus(409);
});
