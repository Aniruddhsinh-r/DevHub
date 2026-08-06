<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

require_once __DIR__.'/../../Helpers/AdminLogin.php';
require_once __DIR__.'/../../Helpers/UserLogin.php';

uses(RefreshDatabase::class);

test('it updates user detail', function () {
    UserLogin();
 
    visit(route('filament.app.auth.profile'))
        ->fill('#form\\.name', 'Rathod Ani')
        ->fill('#form\\.email', 'rathodani@gmail.com')
        ->fill('#form\\.password', 'rathod1290')
        ->fill('#form\\.password_confirmation', 'rathod1290')
        ->press('Save changes')
        ->assertSee('Saved');
 
    $this->assertDatabaseHas('users', [
        'name' => 'Rathod Ani',
        'email' => 'rathodani@gmail.com',
    ]);
});
 
test('guest cant see author profile', function () {
    $user = User::factory()->create();
 
    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertUrlIs(route('filament.app.auth.login'));
});
 
test('Admin cant access follow button profile page', function () {
    AdminLogin();
 
    $user = User::factory()->create();
 
    visit(route('filament.app.resources.users.view', ['record' => $user]))
        ->assertSee('403');
});