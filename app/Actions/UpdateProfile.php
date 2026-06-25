<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Validation\ValidationException;

class UpdateProfile
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $values, ?User $user = null): bool
    {
        $user = Auth::user();

        $data = [
            'name' => $values['name'],
            'email' => $values['email'],
            'bio' => $values['bio'],
        ];

        if (empty($values['password']) && !empty($values['password_confirmation'])) {
            throw ValidationException::withMessages([
                'password' => 'The password field is required when confirming.'
            ]);
        }

        $isBlocked = User::onlyTrashed()->where('email', $values['email'])->exists();
        if ($isBlocked) {
            throw ValidationException::withMessages([
                'email' => 'This email is blocked in our records.',
            ]);
        }

        if (!empty($values['delete_avatar'])) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }
        if (!empty($values['avatar'])) {
            $data['avatar'] = $values['avatar']->store('avatars', 'public');
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        if (!empty($values['password'])) {
            $data['password'] = Hash::make($values['password']);
        }

        return $user->update($data);
    }
}
