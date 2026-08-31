<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

function apiOtherAuthor(): User
{
    Role::firstOrCreate(['name' => UserRole::AUTHOR->value, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    return $user;
}

test('a guest cannot follow a user', function () {
    $target = apiOtherAuthor();

    $response = $this->postJson("/api/v1/user/{$target->uuid}/follow");
    $response->assertStatus(401);
});

test('author can follow another author', function () {
    apiActingAsAuthor([]);
    $target = apiOtherAuthor();

    $response = $this->postJson("/api/v1/user/{$target->uuid}/follow");
    $response->assertCreated()->assertJson(['message' => 'User followed successfully.']);
    $this->assertDatabaseHas('follows', ['followed_id' => $target->id]);
});

test('author cannot follow themselves', function () {
    $user = apiActingAsAuthor([]);

    $response = $this->postJson("/api/v1/user/{$user->uuid}/follow");
    $response->assertForbidden();
});

test('following the same user twice returns a conflict', function () {
    apiActingAsAuthor([]);
    $target = apiOtherAuthor();

    $this->postJson("/api/v1/user/{$target->uuid}/follow")->assertCreated();

    $response = $this->postJson("/api/v1/user/{$target->uuid}/follow");
    $response->assertStatus(409);
});

test('admin or guest cannot follow a user', function () {
    apiActingAsAdmin();
    $target = apiOtherAuthor();

    $response = $this->postJson("/api/v1/user/{$target->uuid}/follow");
    $response->assertForbidden();
});

test('following a non-existent user returns a 404', function () {
    apiActingAsAuthor([]);

    $response = $this->postJson('/api/v1/user/00-00/follow');
    $response->assertNotFound();
});

test('author can unfollow a user they follow', function () {
    apiActingAsAuthor([]);
    $target = apiOtherAuthor();

    $this->postJson("/api/v1/user/{$target->uuid}/follow")->assertCreated();

    $response = $this->deleteJson("/api/v1/user/{$target->uuid}/unfollow");
    $response->assertOk()->assertJson(['message' => 'User unfollowed successfully.']);
});

test('unfollowing a non-existent user returns a 404', function () {
    apiActingAsAuthor([]);

    $response = $this->deleteJson('/api/v1/user/00-00/unfollow');
    $response->assertNotFound();
});

test('admin and guest cannot unfollow a user', function () {
    apiActingAsAdmin();
    $target = apiOtherAuthor();

    $response = $this->deleteJson("/api/v1/user/{$target->uuid}/unfollow");
    $response->assertForbidden();
});

test('prevents duplicate follows at the database level (race condition)', function () {
    $user = User::factory()->create();
    $userToFollow = User::factory()->create();

    DB::table('follows')->insert([
        'follower_id' => $user->id,
        'followed_id' => $userToFollow->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('follows')->insert([
        'follower_id' => $user->id,
        'followed_id' => $userToFollow->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    $this->assertDatabaseCount('follows', 1);
});
