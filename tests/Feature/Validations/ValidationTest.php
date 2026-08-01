<?php

use Livewire\Livewire;
use App\Models\Category;
use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\Pages\CreateArticle;
use App\Filament\Pages\Auth\EditProfile;
use Filament\Auth\Pages\Register;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

require_once __DIR__.'/../Helpers/UserLogin.php';
require_once __DIR__.'/../Helpers/AdminLogin.php';

uses(RefreshDatabase::class);

test('check article validation test', function () {
    UserLogin();
   
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => '',
            'category_id' => 999999,
            'excerpt' => str_repeat('A', 256),
            'body' => '',
            'status' => 'draft',
            'duration' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required'])
        ->assertHasFormErrors(['category_id'])
        ->assertHasFormErrors(['excerpt' => 'max'])
        ->assertHasFormErrors(['body' => 'required']);
});

test('registration fails with a short password', function () {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Someone',
            'email' => 'someone2@example.com',
            'password' => '123',
            'passwordConfirmation' => '123',
        ])
        ->call('register')
        ->assertHasFormErrors(['password']);
 
    $this->assertDatabaseMissing('users', [
        'email' => 'someone2@example.com',
    ]);
});

test('check login validation test', function () {
    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'nobody@example.com',
            'password' => 'pass',
        ])
        ->call('authenticate')
        ->assertHasFormErrors();
});

test('check schedule article validation test require minutes', function () {
    UserLogin();

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Expose Yourself',
            'category_id' => Category::factory()->create(),
            'excerpt' => 'This is a valid excerpt with more than twenty characters.',
            'body' => 'This is a valid article body.',
            'status' => ArticleStatus::SCHEDULED,
            'duration' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'duration' => 'required',
        ]);
});

test('User cant see those auther who dose not exist in db', function () {
    UserLogin();

    $this->get('/profile/4343223')->assertStatus(404);
});

test('Admin can update his profile', function () {
    $admin = AdminLogin();
 
    Livewire::actingAs($admin)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
 
    expect(Hash::check('newPassword', $admin->fresh()->password))->toBeTrue();
});

test('user can update his password', function () {
    $user = UserLogin();
 
    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
 
    expect(Hash::check('newPassword', $user->fresh()->password))->toBeTrue();
});

test('check profile update validation test', function () {
    UserLogin();
 
    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => '',
            'password' => '13333213313',
            'password_confirmation' => 'drafts',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'password' => 'confirmed',
        ]);
});