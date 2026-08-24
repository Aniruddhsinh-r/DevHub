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

test('an authenticated user can list users', function () {
    apiActingAsAuthor([]);
    User::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertOk()->assertJsonStructure(['users' => ['data', 'current_page']]);
});

test('a guest cannot list users', function () {
    $response = $this->getJson('/api/v1/admin/users');

    $response->assertStatus(401);
});

// ----------------------------------------------------------------------
// GET /api/v1/users/{uuid}
// ----------------------------------------------------------------------

test('an admin can view another (non-superadmin) user', function () {
    apiActingAsAdmin();
    $target = User::factory()->create();
    $target->assignRole(UserRole::AUTHOR);

    $response = $this->getJson("/api/v1/users/{$target->uuid}");

    $response->assertOk()->assertJsonPath('user.uuid', $target->uuid);
});

test('an admin cannot view a superadmin user', function () {
    apiActingAsAdmin();
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->getJson("/api/v1/users/{$superAdmin->uuid}");

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

    $response = $this->getJson('/api/v1/users/00000000-0000-0000-0000-000000000000');

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

    $response = $this->deleteJson("/api/v1/users/{$target->uuid}/delete");

    $response->assertOk()->assertJson(['message' => 'User deleted successfully.']);
    $this->assertSoftDeleted('users', ['id' => $target->id]);
});

test('a user cannot delete themselves', function () {
    $user = apiActingAsAdmin(['user.delete']);

    $response = $this->deleteJson("/api/v1/users/{$user->uuid}/delete");

    $response->assertForbidden();
});

test('a superadmin user cannot be deleted', function () {
    apiActingAsAdmin(['user.delete']);
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    $response = $this->deleteJson("/api/v1/users/{$superAdmin->uuid}/delete");

    $response->assertForbidden();
});

test('a user without permission cannot delete another user', function () {
    apiActingAsAuthor([]);
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/{$target->uuid}/delete");

    $response->assertForbidden();
});

// ----------------------------------------------------------------------
// DELETE /api/v1/users/{uuid}/forcedelete
// ----------------------------------------------------------------------

test('a user with permission can permanently delete a soft-deleted user', function () {
    apiActingAsAdmin(['user.forceDelete']);
    $target = User::factory()->create();
    $target->delete();

    $response = $this->deleteJson("/api/v1/users/{$target->uuid}/forcedelete");

    $response->assertOk()->assertJson(['message' => 'User permanently deleted successfully.']);
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('force deleting a user that is not soft-deleted is rejected', function () {
    apiActingAsAdmin(['user.forceDelete']);
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/v1/users/{$target->uuid}/forcedelete");

    $response->assertStatus(422);
});