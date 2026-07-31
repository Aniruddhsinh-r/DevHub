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

// test('Author can see admin profile', function () {
//     $admin = AdminLogin();
//     UserLogin();

//     $response = Livewire::test('livewirecomponent.profile.profile',['user' => $admin]);
//     $response->assertStatus(200);
// });

test('User cant see those auther who dose not exist in db', function () {
    UserLogin();

    $this->get('/profile/4343223')->assertStatus(404);
});

test('Admin cant update his profile', function () {
    $admin = AdminLogin();

    $response = $this->actingAs($admin)->get(route('profile.edit'),[
        'name' => 'Admin Name change',
        'email' => 'admin@example.com',
    ]);
    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'name' => $admin->name,
        'email' => $admin->email,
    ]);
});

test('user can update his password', function () {
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