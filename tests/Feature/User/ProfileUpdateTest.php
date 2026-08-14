<?php

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

require_once __DIR__.'/../Helpers/UserLogin.php';
uses(RefreshDatabase::class);

test('guest is redirected to login when visiting the profile page', function () {
    $this->get(route('filament.app.auth.profile'))
        ->assertRedirect(route('filament.app.auth.login'));
});

test('user can update his profile', function () {
    Storage::fake('public');
    $user = UserLogin();
    $newAvatar = UploadedFile::fake()->image('avatar.jpg');

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => 'Aniruddhsinh Rathod',
            'email' => 'adniruddha@gmail.com',
            'avatar' => $newAvatar,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Aniruddhsinh Rathod',
        'email' => 'adniruddha@gmail.com',
    ]);
});

test('user can update his password', function () {
    $user = UserLogin();

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('password123', $user->fresh()->password))->toBeTrue();
});

test('profile update fails when passwords do not match', function () {
    $user = UserLogin();

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ])
        ->call('save')
        ->assertHasFormErrors(['password' => 'confirmed']);
});

test('profile update fails when email already taken', function () {
    $user = UserLogin();
    $other = User::factory()->create();

    Livewire::actingAs($user)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $user->name,
            'email' => $other->email,
        ])
        ->call('save')
        ->assertHasFormErrors(['email' => 'unique']);
});
