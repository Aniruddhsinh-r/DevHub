<?php

namespace App\Filament\App\Resources\Users;

use App\Filament\App\Resources\Users\Pages\ListUsers;
use App\Filament\App\Resources\Users\Pages\Profile;
use App\Filament\App\Resources\Users\Pages\ViewUser;
use App\Filament\App\Resources\Users\Schemas\UserInfolist;
use App\Filament\App\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\Users\RelationManagers\FollowersRelationManager;
use App\Filament\Resources\Users\RelationManagers\FollowingRelationManager;
use App\Filament\App\Resources\Users\RelationManagers\ArticlesRelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereKeyNot(auth()->id())
            ->role(UserRole::AUTHOR);
    }

    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'User' => $record->name,
            'Email' => $record->email,
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Articles', [
                ArticlesRelationManager::class,
            ]),
            RelationGroup::make('Followers', [
                FollowersRelationManager::class,
            ]),
            RelationGroup::make('Followings', [
                FollowingRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'profile' => Profile::route('/profile'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
