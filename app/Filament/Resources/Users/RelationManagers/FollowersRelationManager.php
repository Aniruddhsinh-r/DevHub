<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FollowersRelationManager extends RelationManager
{
    protected static string $relationship = 'followers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')
            ->columns([
                TextColumn::make('followers.name')
                    ->label('User')
                    ->searchable(),
            ])
            ->headerActions([
                AttachAction::make(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])->emptyStateHeading('This author has no followers yet.');
    }
}
