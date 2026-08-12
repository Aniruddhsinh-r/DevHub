<?php

namespace App\Actions;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class SendInvitation
{
    public function handle(string $email): Invitation
    {
        $email = strtolower(trim($email));

        if (User::onlyTrashed()->where('email', $email)->exists()) {
            throw new RuntimeException('This email is blocked.');
        }

        if (User::where('email', $email)->exists()) {
            throw new RuntimeException(
                'This email is already registered to an active account.'
            );
        }

        $invitation = Invitation::firstOrNew([
            'email' => $email,
        ]);

        if ($invitation->exists && $invitation->expires_at?->isFuture()) {
            throw new RuntimeException(
                "Please wait {$invitation->expires_at->diffForHumans(null, true)} before resending."
            );
        }

        $token = Str::random(32);

        $invitation->fill([
            'token' => $token,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        $invitation->save();

        $url = URL::temporarySignedRoute(
            'invitation-register',
            now()->addMinutes(30),
            ['token' => $token],
        );

        Mail::to($email)->queue(
            new InvitationMail($url)
        );

        return $invitation;
    }
}