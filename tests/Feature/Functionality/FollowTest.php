<?php

use App\Enums\UserRole;
use App\Filament\App\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Helpers/UserLogin.php';
require_once __DIR__.'/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('user can follow but not twice and also unfollow', function () {
    $user = UserLogin();
    $followed = User::factory()->create();
    $followed->assignRole(UserRole::AUTHOR);

    Livewire::test(ViewUser::class, ['record' => $followed->getRouteKey()])
        ->callAction(TestAction::make('follow')->schemaComponent(''));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);

    Livewire::test(ViewUser::class, ['record' => $followed->getRouteKey()])
        ->callAction(TestAction::make('follow')->schemaComponent(''));

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'followed_id' => $followed->id,
    ]);
});

test('admin cant access follow function page', function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);
    $admin = AdminLogin();

    $this->actingAs($admin)
        ->get(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertForbidden();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $admin->id,
    ]);
});

test('Guest cant follow users', function () {
    Role::firstOrCreate(['name' => UserRole::AUTHOR, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::AUTHOR);

    $this->get(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertRedirect(route('filament.app.auth.login'));

    $this->assertDatabaseMissing('follows', [
        'followed_id' => $user->id,
    ]);
});
