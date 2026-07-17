<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                FileUpload::make('avatar')
                    ->disk('s3')
                    ->directory('articleCovers')
                    ->visibility('public'),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ]);
    }
}
