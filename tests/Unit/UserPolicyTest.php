<?php

use App\Enums\UserRole;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Feature/Helpers/AuthorWithPermissions.php';
require_once __DIR__.'/../Feature/Helpers/RoleUser.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new UserPolicy;

    foreach (['user.update', 'user.delete', 'user.manage', 'user.restore', 'user.forceDelete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::SUPERADMIN, 'guard_name' => 'web']);
});

test('an author can follow another author', function () {
    $follower = roleUser(UserRole::AUTHOR);
    $target = roleUser(UserRole::AUTHOR);

    expect($this->policy->follow($follower, $target))->toBeTrue();
});

test('an author cannot follow themself', function () {
    $author = roleUser(UserRole::AUTHOR);

    expect($this->policy->follow($author, $author))->toBeFalse();
});

test('an author cannot follow an admin', function () {
    $follower = roleUser(UserRole::AUTHOR);
    $admin = roleUser(UserRole::ADMIN);

    expect($this->policy->follow($follower, $admin))->toBeFalse();
});

test('an admin cannot follow an author', function () {
    $admin = roleUser(UserRole::ADMIN);
    $author = roleUser(UserRole::AUTHOR);

    expect($this->policy->follow($admin, $author))->toBeFalse();
});

test('a superadmin can view any user', function () {
    $superAdmin = roleUser(UserRole::SUPERADMIN);
    $anyUser = roleUser(UserRole::ADMIN);

    expect($this->policy->view($superAdmin, $anyUser))->toBeTrue();
});

test('an admin can view another admin but not a superadmin', function () {
    $admin = roleUser(UserRole::ADMIN);
    $otherAdmin = roleUser(UserRole::ADMIN);
    $superAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->view($admin, $otherAdmin))->toBeTrue();
    expect($this->policy->view($admin, $superAdmin))->toBeFalse();
});

test('an author can view another author but not an admin', function () {
    $author = roleUser(UserRole::AUTHOR);
    $otherAuthor = roleUser(UserRole::AUTHOR);
    $admin = roleUser(UserRole::ADMIN);

    expect($this->policy->view($author, $otherAuthor))->toBeTrue();
    expect($this->policy->view($author, $admin))->toBeFalse();
});

test('a user cannot remove themself', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.manage');

    expect($this->policy->remove($admin, $admin))->toBeFalse();
});

test('nobody can remove a superadmin, even another superadmin', function () {
    $superAdmin = roleUser(UserRole::SUPERADMIN);
    $superAdmin->givePermissionTo('user.manage');
    $targetSuperAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->remove($superAdmin, $targetSuperAdmin))->toBeFalse();
});

test('an admin with user.manage permission can remove a regular author', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.manage');
    $author = roleUser(UserRole::AUTHOR);

    expect($this->policy->remove($admin, $author))->toBeTrue();
});

test('a user cannot delete themself even with permission', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.delete');

    expect($this->policy->delete($admin, $admin))->toBeFalse();
});

test('a superadmin cannot be deleted', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.delete');
    $superAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->delete($admin, $superAdmin))->toBeFalse();
});

test('restore requires both delete eligibility and the restore permission', function () {
    $admin = roleUser(UserRole::ADMIN);
    $author = roleUser(UserRole::AUTHOR);

    $admin->givePermissionTo('user.delete');
    expect($this->policy->restore($admin, $author))->toBeFalse();

    $admin->givePermissionTo('user.restore');
    expect($this->policy->restore($admin, $author))->toBeTrue();
});

test('a superadmin can never be force-deleted regardless of permission', function () {
    $superAdmin = roleUser(UserRole::SUPERADMIN);
    $superAdmin->givePermissionTo('user.forceDelete');
    $targetSuperAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->forceDelete($superAdmin, $targetSuperAdmin))->toBeFalse();
});

test('a user can always update their own profile', function () {
    $author = roleUser(UserRole::AUTHOR);

    expect($this->policy->update($author, $author))->toBeTrue();
});

test('nobody but the superadmin themself can update a superadmin', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.update');
    $superAdmin = roleUser(UserRole::SUPERADMIN);

    expect($this->policy->update($admin, $superAdmin))->toBeFalse();
    expect($this->policy->update($superAdmin, $superAdmin))->toBeTrue();
});

test('an admin cannot update a trashed user even with permission', function () {
    $admin = roleUser(UserRole::ADMIN);
    $admin->givePermissionTo('user.update');
    $target = roleUser(UserRole::AUTHOR);
    $target->delete();

    expect($this->policy->update($admin, $target))->toBeFalse();
});

test('a superadmin cannot update a trashed user', function () {
    $superAdmin = roleUser(UserRole::SUPERADMIN);
    $target = roleUser(UserRole::AUTHOR);
    $target->delete();

    expect($this->policy->update($superAdmin, $target))->toBeFalse();
});
