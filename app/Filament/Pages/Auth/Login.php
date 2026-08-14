<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use Filament\Auth\Pages\Login as PagesLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Login extends PagesLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('email'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->autocomplete('current-password'),
                Checkbox::make('remember')
                    ->label('Remember me'),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        session()->forget('url.intended');

        if (auth()->user()->hasRole([
            UserRole::ADMIN,
            UserRole::SUPERADMIN,
        ])) {
            return '/admin';
        }

        return '/home';
    }
}
