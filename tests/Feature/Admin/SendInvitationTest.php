<?php

use App\Actions\SendInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('handle normalizes email casing and whitespace before storing', function () {
    Mail::fake();

    app(SendInvitation::class)->handle('  MixedCase@Example.com  ');

    $this->assertDatabaseHas('invitations', ['email' => 'mixedcase@example.com']);
});

test('handle blocks invitations to a soft-deleted user email', function () {
    Mail::fake();

    $trashed = User::factory()->create(['email' => 'blocked@example.com']);
    $trashed->delete();

    expect(fn () => app(SendInvitation::class)->handle('blocked@example.com'))
        ->toThrow(RuntimeException::class, 'This email is blocked.');

    $this->assertDatabaseMissing('invitations', ['email' => 'blocked@example.com']);
});

test('handle prevents resending while an invitation is still pending and unexpired', function () {
    Mail::fake();

    Invitation::factory()->create([
        'email' => 'stillpending@example.com',
        'status' => 'pending',
        'expires_at' => now()->addMinutes(20),
    ]);

    expect(fn () => app(SendInvitation::class)->handle('stillpending@example.com'))
        ->toThrow(RuntimeException::class);
});
