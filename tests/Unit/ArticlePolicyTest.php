<?php

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Feature/Helpers/AuthorWithPermissions.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ArticlePolicy;

    foreach ([
        'article.create', 'article.edit', 'article.delete',
        'article.bookmark', 'article.comment',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => UserRole::SUPERADMIN, 'guard_name' => 'web']);
});

test('a user without the article.create permission cannot create an article', function () {
    $user = authorWithPermissions([]);

    expect($this->policy->create($user))->toBeFalse();
});

test('a user with the article.create permission can create an article', function () {
    $user = authorWithPermissions(['article.create']);

    expect($this->policy->create($user))->toBeTrue();
});

test('the article owner with article.edit permission can update their own article', function () {
    $owner = authorWithPermissions(['article.edit']);
    $article = Article::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update($owner, $article))->toBeTrue();
});

test('the article owner without article.edit permission cannot update their own article', function () {
    $owner = authorWithPermissions([]);
    $article = Article::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update($owner, $article))->toBeFalse();
});

test('a user with article.edit permission cannot update someone elses article', function () {
    $user = authorWithPermissions(['article.edit']);
    $article = Article::factory()->create();

    expect($this->policy->update($user, $article))->toBeFalse();
});

test('an admin can update any article regardless of permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::ADMIN);
    $article = Article::factory()->create();

    expect($this->policy->update($admin, $article))->toBeTrue();
});

test('a superadmin can update any article regardless of permissions', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);
    $article = Article::factory()->create();

    expect($this->policy->update($superAdmin, $article))->toBeTrue();
});

test('the article owner with article.delete permission can delete their own article', function () {
    $owner = authorWithPermissions(['article.delete']);
    $article = Article::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->delete($owner, $article))->toBeTrue();
});

test('a user cannot delete someone elses article even with article.delete permission', function () {
    $user = authorWithPermissions(['article.delete']);
    $article = Article::factory()->create();

    expect($this->policy->delete($user, $article))->toBeFalse();
});

test('anyone can view a published article', function () {
    $user = authorWithPermissions([]);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    expect($this->policy->view($user, $article))->toBeTrue();
});

test('the owner can view their own draft article', function () {
    $owner = authorWithPermissions([]);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT, 'user_id' => $owner->id]);

    expect($this->policy->view($owner, $article))->toBeTrue();
});

test('a non-owner cannot view someone elses draft article', function () {
    $user = authorWithPermissions([]);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    expect($this->policy->view($user, $article))->toBeFalse();
});

test('an admin can view any draft article', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::ADMIN);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    expect($this->policy->view($admin, $article))->toBeTrue();
});

test('a user with permission can like a published article that is not their own', function () {
    $user = authorWithPermissions(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

    expect($this->policy->like($user, $article))->toBeTrue();
});

test('a user cannot like their own article even when published', function () {
    $owner = authorWithPermissions(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED, 'user_id' => $owner->id]);

    expect($this->policy->like($owner, $article))->toBeFalse();
});

test('a user cannot like a draft article', function () {
    $user = authorWithPermissions(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    expect($this->policy->like($user, $article))->toBeFalse();
});

test('a user cannot bookmark their own published article', function () {
    $owner = authorWithPermissions(['article.bookmark']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED, 'user_id' => $owner->id]);

    expect($this->policy->bookmark($owner, $article))->toBeFalse();
});

test('a user can comment on a published article that is their own', function () {
    $owner = authorWithPermissions(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED, 'user_id' => $owner->id]);

    expect($this->policy->comment($owner, $article))->toBeTrue();
});

test('a user cannot comment on a draft article', function () {
    $user = authorWithPermissions(['article.comment']);
    $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

    expect($this->policy->comment($user, $article))->toBeFalse();
});

test('an admin cannot update a trashed article', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::ADMIN);
    $article = Article::factory()->create();
    $article->delete();

    expect($this->policy->update($admin, $article))->toBeFalse();
});

test('a superadmin cannot update a trashed article', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(UserRole::SUPERADMIN);
    $article = Article::factory()->create();
    $article->delete();

    expect($this->policy->update($superAdmin, $article))->toBeFalse();
});
