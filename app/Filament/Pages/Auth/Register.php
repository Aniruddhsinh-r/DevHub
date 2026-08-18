<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use Filament\Auth\Pages\Register as PagesRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class Register extends PagesRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->minLength(4)
                    ->maxLength(50),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(table: 'users', column: 'email')
                    ->minLength(10)
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
