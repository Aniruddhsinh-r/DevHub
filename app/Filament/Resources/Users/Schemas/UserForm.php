<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->minLength(5)
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->maxLength(255)
                    ->required(),
                FileUpload::make('avatar')
                    ->disk('public')
                    ->directory('articleCovers')
                    ->visibility('public'),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8) 
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->confirmed()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->required(fn (string $operation): bool => $operation === 'create'),
            ]);
    }
}
