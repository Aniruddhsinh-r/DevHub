<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as PagesEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends PagesEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label('Profile Photo')
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->avatar(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(table: 'users', column: 'email')
                    ->maxLength(255),
                TextInput::make('bio')
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->minLength(8)
                    ->maxLength(255)
                    ->revealable()
                    ->confirmed()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(
                        fn (string $state): string => Hash::make($state)
                    ),
                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
            ]);
    }

    protected function getRedirectUrl(): ?string
    {
        return filament()->getUrl();
    }
}
