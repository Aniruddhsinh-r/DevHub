<?php

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/AdminLogin.php';
require_once __DIR__.'/../Helpers/UserLogin.php';
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

test('admin cant view a superadmin profile', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('superadmin');
    AdminLogin();

    visit('/admin/users/'.$superAdmin->uuid)
        ->assertSee('404');
});

test('admin cant open the edit page of a superadmin', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('superadmin');
    AdminLogin();

    visit('/admin/users/'.$superAdmin->uuid.'/edit')
        ->assertSee('404');

    $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'deleted_at' => null]);
});

test('superadmin can still edit their own profile', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('superadmin');
 
    $this->actingAs($superAdmin)
        ->get('/admin/profile')
        ->assertSuccessful();
});

test('admin can edit a user', function () {
    AdminLogin();

    $user = User::factory()->create(['name' => 'old name']);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'updated name',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
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

test('admin cannot see or delete himself', function () {
    $admin = AdminLogin();

    Livewire::test(ListUsers::class)
        ->assertCanNotSeeTableRecords(
            User::whereKey($admin->id)->get()
        );

    expect($admin->can('delete', $admin))->toBeFalse();
});

test('admin cannot see or delete superadmin', function () {
    AdminLogin();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);

    Livewire::test(ListUsers::class)
        ->assertCanNotSeeTableRecords(
            User::whereKey($superAdmin->id)->get()
        );

    expect(auth()->user()->can('delete', $superAdmin))->toBeFalse();
});

test('admin cannot edit user password becasue form has no password field', function () {
    AdminLogin();
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'password' => 'attemptedNewPassword',
            'password_confirmation' => 'attemptedNewPassword',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->password)->toBe($originalPassword);
});