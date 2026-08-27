<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
});

// ----------------------------------------------------------------------
// GET /api/v1/profile
// ----------------------------------------------------------------------

test('a guest cannot view the profile endpoint', function () {
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
});

test('an authenticated user can view their own profile', function () {
    $user = apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/profile');

    $response->assertOk()->assertJsonPath('user.email', $user->email);
});

// ----------------------------------------------------------------------
// PUT /api/v1/profile/update
// ----------------------------------------------------------------------

test('a user can update their own profile', function () {
    apiActingAsAuthor([]);

    $response = $this->putJson('/api/v1/profile/update', ['name' => 'Updated Name']);

    $response->assertOk()->assertJsonPath('user.name', 'Updated Name');
});

test('profile update fails when the email is already taken by someone else', function () {
    apiActingAsAuthor([]);
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->putJson('/api/v1/profile/update', ['email' => 'taken@example.com']);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('profile update fails when password confirmation does not match', function () {
    apiActingAsAuthor([]);

    $response = $this->putJson('/api/v1/profile/update', [
        'password' => 'newpassword123',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['password']);
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/users
// ----------------------------------------------------------------------

// ----------------------------------------------------------------------
// GET /api/v1/users  (author-only listing — index())
// ----------------------------------------------------------------------

test('a guest cannot list users via the author listing', function () {
    $response = $this->getJson('/api/v1/users');

    $response->assertStatus(401);
});

test('a non-author (admin) cannot access the author-only user listing', function () {
    apiActingAsAdmin();

    $response = $this->getJson('/api/v1/users');

    $response->assertForbidden();
});

test('an author can list users and only sees other authors', function () {
    $author = apiActingAsAuthor([]);

    $otherAuthor = User::factory()->create();
    $otherAuthor->assignRole(UserRole::AUTHOR);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::ADMIN);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->getJson('/api/v1/users');
    $response->assertOk();

    $uuids = collect($response->json('data'))->pluck('uuid');

    expect($uuids)->toContain($otherAuthor->uuid);
    expect($uuids)->not->toContain($admin->uuid);
    expect($uuids)->not->toContain($superAdmin->uuid);
});

test('the author-only user listing never includes the requesting user themself', function () {
    $author = apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/users');
    $response->assertOk();

    $uuids = collect($response->json('data'))->pluck('uuid');

    expect($uuids)->not->toContain($author->uuid);
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/users  (admin-only listing — intended to be adminRecords())
//
// NOTE: this currently hits UserController::index() instead of
// adminRecords() due to the duplicate route registration bug (see routes/api.php).
// These tests assert the INTENDED behavior once that's fixed: everyone
// except superadmins and the requester themself.
// ----------------------------------------------------------------------

test('a guest cannot list users via the admin listing', function () {
    $response = $this->getJson('/api/v1/admin/users');

    $response->assertStatus(401);
});

test('a non-admin (author) cannot access the admin user listing', function () {
    apiActingAsAuthor([]);

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertForbidden();
});

test('an admin can list users and never sees a superadmin', function () {
    apiActingAsAdmin();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->getJson('/api/v1/admin/users');
    $response->assertOk();

    $uuids = collect($response->json('data'))->pluck('uuid');

    expect($uuids)->not->toContain($superAdmin->uuid);
});

test('the admin user listing never includes the requesting admin themself', function () {
    $admin = apiActingAsAdmin();

    $response = $this->getJson('/api/v1/admin/users');
    $response->assertOk();

    $uuids = collect($response->json('data'))->pluck('uuid');

    expect($uuids)->not->toContain($admin->uuid);
});

test('the admin user listing includes other admins and authors alike', function () {
    apiActingAsAdmin();

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::ADMIN);

    $author = User::factory()->create();
    $author->assignRole(UserRole::AUTHOR);

    $response = $this->getJson('/api/v1/admin/users');
    $response->assertOk();

    $uuids = collect($response->json('data'))->pluck('uuid');

    expect($uuids)->toContain($otherAdmin->uuid);
    expect($uuids)->toContain($author->uuid);
});

// ----------------------------------------------------------------------
// GET /api/v1/admin/users/{uuid}  (admin viewing another user — show())
// ----------------------------------------------------------------------

test('an admin can view another (non-superadmin) user', function () {
    apiActingAsAdmin();
    $target = User::factory()->create();
    $target->assignRole(UserRole::AUTHOR);

    $response = $this->getJson("/api/v1/admin/users/{$target->uuid}");

    $response->assertOk()->assertJsonPath('user.uuid', $target->uuid);
});

test('an admin cannot view a superadmin user', function () {
    apiActingAsAdmin();
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->getJson("/api/v1/admin/users/{$superAdmin->uuid}");

    $response->assertForbidden();
});

test('an author can only view other authors', function () {
    apiActingAsAuthor([]);
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::ADMIN);

    $response = $this->getJson("/api/v1/users/{$admin->uuid}");

    $response->assertForbidden();
});

test('viewing a non-existent user returns a 404', function () {
    apiActingAsAdmin();

    $response = $this->getJson('/api/v1/admin/users/00000000-0000-0000-0000-000000000000');

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// PUT /api/v1/admin/users/{uuid}/edit
// ----------------------------------------------------------------------

test('a user with permission can edit another user', function () {
    apiActingAsAdmin(['user.update']);
    $target = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$target->uuid}/edit", ['name' => 'Edited Name']);

    $response->assertOk()->assertJsonPath('user.name', 'Edited Name');
});

test('a user without permission cannot edit another user', function () {
    apiActingAsAuthor([]);
    $target = User::factory()->create();

    $response = $this->putJson("/api/v1/admin/users/{$target->uuid}/edit", ['name' => 'Edited Name']);

    $response->assertForbidden();
});

test('editing a non-existent user returns a 404', function () {
    apiActingAsAdmin(['user.update']);

    $response = $this->putJson('/api/v1/admin/users/00000000-0000-0000-0000-000000000000/edit', ['name' => 'Edited Name']);

    $response->assertNotFound();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/users/{uuid}/delete
// ----------------------------------------------------------------------

test('a user with permission can delete another user', function () {
    apiActingAsAdmin(['user.delete']);
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$target->uuid}/delete");

    $response->assertNoContent();
    $this->assertSoftDeleted('users', ['id' => $target->id]);
});

test('a user cannot delete themselves', function () {
    $user = apiActingAsAdmin(['user.delete']);

    $response = $this->deleteJson("/api/v1/admin/users/{$user->uuid}/delete");

    $response->assertForbidden();
});

test('a superadmin user cannot be deleted', function () {
    apiActingAsAdmin(['user.delete']);
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->deleteJson("/api/v1/admin/users/{$superAdmin->uuid}/delete");

    $response->assertForbidden();
});

test('a user without permission cannot delete another user', function () {
    apiActingAsAuthor([]);
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$target->uuid}/delete");

    $response->assertForbidden();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/users/{uuid}/forcedelete
// ----------------------------------------------------------------------

test('a user with permission can permanently delete a soft-deleted user', function () {
    apiActingAsAdmin(['user.forceDelete']);
    $target = User::factory()->create();
    $target->delete();

    $response = $this->deleteJson("/api/v1/admin/users/{$target->uuid}/forcedelete");

    $response->assertNoContent();
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('force deleting a user that is not soft-deleted is rejected', function () {
    apiActingAsAdmin(['user.forceDelete']);
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/users/{$target->uuid}/forcedelete");

    $response->assertStatus(422);
});
