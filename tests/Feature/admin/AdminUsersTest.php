<?php

use App\Models\User;
use App\Models\Article;
use Livewire\Livewire;
use App\Models\Comment;
use App\Models\Like;
use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ViewUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

require_once __DIR__ . '/../Helpers/AdminLogin.php';
require_once __DIR__ . '/../Helpers/UserLogin.php';
uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
});

test('guest is redirected to login when visiting the users page', function () {
    $this->get('/admin/users')
        ->assertRedirect('/admin/login');
});

test('admin can render the users list page', function () {
    AdminLogin();

    Livewire::test(ListUsers::class)
        ->assertSuccessful();
});

test('Admin User find test', function () {
    AdminLogin();

    $user = User::factory()->create([
        'name' => 'ishigory',
    ]);
    $user->assignRole(UserRole::AUTHOR);

    Livewire::test(ListUsers::class)
        ->searchTable('ishigory')
        ->assertCanSeeTableRecords([$user]);
});

test('admin can view a user profile', function () {
    AdminLogin();

    $user = User::factory()->create();

    Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($user->name);
});

// test('admin can create a user', function () {
//     AdminLogin();

//     Livewire::test(CreateUser::class)
//         ->fillForm([
//             'uuid' => (string) Illuminate\Support\Str::uuid(),
//             'name' => 'new author',
//             'email' => 'newauthor@example.com',
//             'password' => 'password',
//         ])
//         ->call('create')
//         ->assertHasNoFormErrors();

//     $this->assertDatabaseHas('users', [
//         'name' => 'new author',
//         'email' => 'newauthor@example.com',
//     ]);
// });

test('admin can edit a user', function () {
    AdminLogin();

    $user = User::factory()->create(['name' => 'old name']);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'updated name',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'updated name',
    ]);
});

test('Admin User delete test', function () {
    AdminLogin();

    $removeuser = User::factory()->create();
    $removeuser->assignRole(UserRole::AUTHOR);
    $article = Article::factory()->create(['user_id' => $removeuser->id]);

    Comment::factory()->create(['user_id' => $removeuser->id, 'article_id' => $article->id]);
    Like::factory()->create(['user_id' => $removeuser->id]);

    Livewire::test(ListUsers::class)
        ->callTableAction('delete', $removeuser);

    $this->assertSoftDeleted('users', ['id' => $removeuser->id]);
    $this->assertSoftDeleted('articles', ['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('bookmarks', ['user_id' => $removeuser->id]);
    $this->assertSoftDeleted('comments', ['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('likes', ['user_id' => $removeuser->id]);
    $this->assertDatabaseMissing('views', ['user_id' => $removeuser->id]);
});

test('admin can restore a deleted user', function () {
    AdminLogin();

    $user = User::factory()->create();
    $user->delete();

    Livewire::test(ListUsers::class)
        ->filterTable('trashed')
        ->callTableAction('restore', $user);

    $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
});

test('author cannot access admin users page', function () {
    UserLogin();

    $this->get('/admin/users')
        ->assertForbidden();
});
