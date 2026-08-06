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
                FileUpload::make('avatar')
                    ->label('Profile Photo')
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->avatar(),
                TextInput::make('name')
                    ->required()
                    ->minLength(5)
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(table: 'users', column: 'email')
                    ->required()
                    ->maxLength(255),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
