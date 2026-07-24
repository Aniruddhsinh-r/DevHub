<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Tables\Table;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;

class LatestArticles extends TableWidget
{
    protected static ?string $heading = 'Latest Articles';

    protected int|string|array $columnSpan = 2;
    
    protected static ?int $sort = 2;
    
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Article::query()->latest()->take(6)
            )
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('view', [
                'record' => $record,
            ]))
            ->columns([
                TextColumn::make('title')
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Author'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->since(),
            ]);
    }
}