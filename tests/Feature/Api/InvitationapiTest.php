<?php

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

// ----------------------------------------------------------------------
// POST /api/v1/admin/invitation/send
// ----------------------------------------------------------------------

test('a guest cannot send an invitation', function () {
    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'invitee@example.com']);

    $response->assertStatus(401);
});

test('only admin can send an invitation', function () {
    Mail::fake();
    apiActingAsAdmin(['user.manage']);
    
    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'invitee@example.com']);

    $response->assertCreated()->assertJsonPath('invitation.email', 'invitee@example.com');
    $this->assertDatabaseHas('invitations', ['email' => 'invitee@example.com', 'status' => 'pending']);
    Mail::assertQueued(InvitationMail::class);
});

test('an admin cannot send an invitation to a blocked (soft-deleted) user\'s email', function () {
    apiActingAsAdmin(['user.manage']);
    $blockedUser = User::factory()->create(['email' => 'blocked@example.com']);
    $blockedUser->delete();

    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'blocked@example.com']);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('user and guest cannot send an invitation', function () {
    apiActingAsAuthor([]);

    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'invitee@example.com']);

    $response->assertForbidden();
});

test('sending an invitation requires a valid email', function () {
    apiActingAsAdmin(['user.manage']);

    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'not-an-email']);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('admin can not send invitation to registered email', function () {
    apiActingAsAdmin(['user.manage']);
    $existingUser = User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'taken@example.com']);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('sending a duplicate pending invitation returns a conflict', function () {
    Mail::fake();
    apiActingAsAdmin(['user.manage']);

    $this->postJson('/api/v1/admin/invitation/send', ['email' => 'invitee@example.com'])->assertCreated();
    $response = $this->postJson('/api/v1/admin/invitation/send', ['email' => 'invitee@example.com']);

    $response->assertStatus(409);
});

test('admin cannot resend invitation to soft-deleted user email', function () {
    apiActingAsAdmin(['user.manage']);
    $blockedUser = User::factory()->create(['email' => 'blocked@example.com']);
    $blockedUser->delete();

    $invitation = Invitation::factory()->create([
        'email' => 'blocked@example.com',
        'status' => 'expired',
    ]);

    $response = $this->postJson("/api/v1/admin/invitation/{$invitation->id}/resend");

    $response->assertStatus(422)->assertJson(['message' => 'This email is blocked.']);
});

// ----------------------------------------------------------------------
// POST /api/v1/admin/invitation/{invitation}/resend
// ----------------------------------------------------------------------

test('admin can resend a expired invitation', function () {
    Mail::fake();
    apiActingAsAdmin(['user.manage']);
    $invitation = Invitation::factory()->create(['status' => 'expired', 'expires_at' => now()->subMinutes(5)]);

    $response = $this->postJson("/api/v1/admin/invitation/{$invitation->id}/resend");

    $response->assertOk()->assertJson(['message' => 'invitation resend successfully']);
    Mail::assertQueued(InvitationMail::class);
});

test('only expired invitation will be resent', function () {
    apiActingAsAdmin(['user.manage']);
    $invitation = Invitation::factory()->create(['status' => 'accepted']);

    $response = $this->postJson("/api/v1/admin/invitation/{$invitation->id}/resend");

    $response->assertStatus(422)->assertJson(['message' => 'Only expired invitations can be resent.']);
});

test('author and guest cannot resend an invitation', function () {
    apiActingAsAuthor([]);
    $invitation = Invitation::factory()->create(['status' => 'pending']);

    $response = $this->postJson("/api/v1/admin/invitation/{$invitation->id}/resend");

    $response->assertForbidden();
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/invitations
// ----------------------------------------------------------------------

test('only admin can see invitation list', function () {
    apiActingAsAdmin(['user.manage']);
    Invitation::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/invitations');

    $response->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page'],]);
});

test('author can guest cannot see invitation list', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/admin/invitations');

    $response->assertForbidden();
});

test('invitations can be searched', function () {
    apiActingAsAdmin(['user.manage']);
    Invitation::factory()->create(['email' => 'findme@example.com']);
    Invitation::factory()->create(['email' => 'other@example.com']);

    $response = $this->getJson('/api/v1/admin/invitations?search=findme');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/invitation/{invitation}
// ----------------------------------------------------------------------

test('admin can view a single invitation', function () {
    apiActingAsAdmin(['user.manage']);
    $invitation = Invitation::factory()->create();

    $response = $this->getJson("/api/v1/admin/invitation/{$invitation->id}");

    $response->assertOk()->assertJsonPath('invitation.id', $invitation->id);
});

test('author and user cannot view a single invitation', function () {
    apiActingAsAuthor([]);
    $invitation = Invitation::factory()->create();

    $response = $this->getJson("/api/v1/admin/invitation/{$invitation->id}");

    $response->assertForbidden();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/admin/invitation/{invitation}/delete
// ----------------------------------------------------------------------

test('asmin can delete an invitation', function () {
    apiActingAsAdmin(['user.manage']);
    $invitation = Invitation::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/invitation/{$invitation->id}/delete");

    $response->assertNoContent();
    $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
});

test('a user without permission cannot delete an invitation', function () {
    apiActingAsAuthor([]);
    $invitation = Invitation::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/invitation/{$invitation->id}/delete");

    $response->assertForbidden();
});
