<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/ApiHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
});

test('user can register via the api', function () {
    $payload = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/v1/register', $payload);

    $response->assertCreated()
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonPath('user.email', 'johndoe@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'johndoe@example.com',
    ]);
});

test('register fails when required fields are missing', function () {
    $response = $this->postJson('/api/v1/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('register fails with an invalid email', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('register fails when the email is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Jane Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

test('register fails when the password is too short', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'John Doe',
        'email' => 'johndoe2@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['password']);
});

test('an author can log in via the api', function () {
    $user = apiAuthorForLogin('secret123');

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonPath('user.email', $user->email);
});

test('login fails with incorrect credentials', function () {
    $user = apiAuthorForLogin('secret123');

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'The provided credentials do not match our records.']);
});

test('login fails for a non-existent email', function () {
    $response = $this->postJson('/api/v1/login', [
        'email' => 'nobody@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(401);
});

test('login fails for a user without the author role', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertStatus(401);
});

test('login validation requires email and password', function () {
    $response = $this->postJson('/api/v1/login', []);

    $response->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
});

// ----------------------------------------------------------------------
// POST /api/v1/admin/login
// ----------------------------------------------------------------------

test('an admin can log in via the admin api endpoint', function () {
    $user = apiAdminForLogin('adminpass123');

    $response = $this->postJson('/api/v1/admin/login', [
        'email' => $user->email,
        'password' => 'adminpass123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user', 'token'])
        ->assertJsonPath('user.email', $user->email);
});

test('admin login fails for a non-admin user', function () {
    $user = apiAuthorForLogin('authorpass123');

    $response = $this->postJson('/api/v1/admin/login', [
        'email' => $user->email,
        'password' => 'authorpass123',
    ]);

    $response->assertStatus(401);
});

test('admin login fails with a wrong password', function () {
    $user = apiAdminForLogin('adminpass123');

    $response = $this->postJson('/api/v1/admin/login', [
        'email' => $user->email,
        'password' => 'incorrect',
    ]);

    $response->assertStatus(401);
});

test('a guest cannot logout', function () {
    $response = $this->postJson('/api/v1/logout');
 
    $response->assertStatus(401);
});
 
test('an authenticated user can logout and their token is revoked', function () {
    $user = apiAuthorForLogin('password123');
 
    $token = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertOk()->json('token');
 
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout');
 
    $response->assertOk()->assertJson(['message' => 'Logged out successfully.']);
 
    $this->assertDatabaseCount('personal_access_tokens', 0);
});
 
test('a revoked token can no longer access protected routes after logout', function () {
    $user = apiAuthorForLogin('password123');
 
    $token = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertOk()->json('token');
 
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout')
        ->assertOk();
        
    Auth::forgetGuards();
 
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/profile')
        ->assertStatus(401);
});
 
test('logout with a garbage or missing token returns unauthenticated', function () {
    $response = $this->withHeader('Authorization', 'Bearer this-token-does-not-exist')
        ->postJson('/api/v1/logout');
 
    $response->assertStatus(401);
});
 
test('logout only revokes the token used for that request, not the user\'s other tokens', function () {
    $user = apiAuthorForLogin('password123');
 
    $tokenA = $user->createToken('device-a')->plainTextToken;
    $tokenB = $user->createToken('device-b')->plainTextToken;
 
    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->postJson('/api/v1/logout')
        ->assertOk();
 
    Auth::forgetGuards();
 
    // token A is dead
    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/profile')
        ->assertStatus(401);
 
    Auth::forgetGuards();
 
    // token B is still valid
    $this->withHeader('Authorization', "Bearer {$tokenB}")
        ->getJson('/api/v1/profile')
        ->assertOk();
});


if (! function_exists('apiAuthorForLogin')) {
    function apiAuthorForLogin(string $password): User
    {
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user->assignRole(UserRole::AUTHOR);

        return $user;
    }
}

if (! function_exists('apiAdminForLogin')) {
    function apiAdminForLogin(string $password): User
    {
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user->assignRole(UserRole::ADMIN);

        return $user;
    }
}