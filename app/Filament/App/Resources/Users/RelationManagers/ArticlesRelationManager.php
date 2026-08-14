<?php

namespace App\Filament\App\Resources\Users\RelationManagers;

use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'articles';

    protected static ?string $relatedResource = ArticleResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('status', ArticleStatus::PUBLISHED);
            })
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('excerpt'),
                TextColumn::make('views_count')
                    ->counts('views')
                    ->label('views'),
                TextColumn::make('likes_count')
                    ->counts('likes')
                    ->label('Likes'),
            ])->recordUrl(fn ($record) => ArticleResource::getUrl('view', [
                'record' => $record,
            ]));
    }
}
