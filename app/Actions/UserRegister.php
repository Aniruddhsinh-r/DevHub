<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Events\UserCreate;
use App\Enums\UserRole;

use Illuminate\Container\Attributes\CurrentUser;

class UserRegister
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $values, ?User $user = null): void
    {
        $avatarPath = null;
        if ($values['avatar'] ?? false) {
            $avatarPath = $values['avatar']->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $values['name'],
            'email' => strtolower($values['email']),
            'password' => Hash::make($values['password']),
            'bio' => $values['bio'],
            'avatar'=> $avatarPath,
        ]);

        $user->assignRole(UserRole::AUTHOR);
        Auth::login($user, $remember = true);

        UserCreate::dispatch();
        
        $to = $values['email'];
        $message = $user->name;

        // Mail::to($to)->queue(new RegistrationMail($message));
    }
}