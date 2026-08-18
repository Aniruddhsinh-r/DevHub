<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->minLength(4)
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->minLength(10)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
