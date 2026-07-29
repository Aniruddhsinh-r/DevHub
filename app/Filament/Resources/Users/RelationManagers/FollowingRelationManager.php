<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FollowingRelationManager extends RelationManager
{
    protected static string $relationship = 'following';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('following')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])->emptyStateHeading('This author is not following anyone.')
            ->recordUrl(fn ($record) => UserResource::getUrl('view', [
                'record' => $record,
            ]));
    }
}
