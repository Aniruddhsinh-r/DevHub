<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Attributes\CurrentUser;

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