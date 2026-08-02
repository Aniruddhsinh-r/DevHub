<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as PagesLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Enums\UserRole;

use Filament\Pages\Page;

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
