<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;

class LikesRelationManager extends RelationManager
{
    protected static string $relationship = 'likes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('likes.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->since(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])->emptyStateHeading('No likes to display.');
    }
}
