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
                    ->avatar()
                    ->imageEditor(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
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
