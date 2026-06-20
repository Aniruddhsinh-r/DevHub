<?php

use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Role::firstOrCreate([
        'name' => 'author',
        'guard_name' => 'web'
    ]);
});

test('user registration test', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('avatar.jpg');
    $email = 'khabib'.time().'@gmail.com';

    Livewire::test('livewirecomponent.auth.register')
        ->set('name', 'khabibji')
        ->set('email', $email)
        ->set('password', 'password123')
        ->set('bio', 'Hello, this is my bio.')
        ->set('avatar', $file)
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'name' => 'khabibji',
        'email' => $email,
    ]);
});
