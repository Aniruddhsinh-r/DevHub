<?php

namespace App\Filament\App\Resources\Users\Tables;

use App\Enums\ArticleStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->imageWidth(40)
                    ->defaultImageUrl('https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_1280.png'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('articles_count')
                    ->counts(['articles' => fn ($query) => $query->where('status', ArticleStatus::PUBLISHED)])
                    ->label('Articles')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
