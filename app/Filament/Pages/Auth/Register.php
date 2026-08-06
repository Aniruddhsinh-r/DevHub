<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as PagesRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use App\Enums\UserRole;

use Filament\Pages\Page;

class Register extends PagesRegister
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
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->minLength(8)
                    ->maxLength(255)
                    ->revealable()
                    ->required(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);
        $user->assignRole(UserRole::AUTHOR);

        return $user;
    }
}
