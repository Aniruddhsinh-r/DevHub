<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use App\Enums\ArticleStatus;
use App\Filament\App\Resources\Articles\Tables\ArticleTable;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('myarticle')
                ->label('My Articles')
                ->url('/articles/my-articles')
                ->color('primary'),
            Action::make('create')
                ->label('Create Article')
                ->icon('heroicon-o-plus')
                ->url(ArticleResource::getUrl('create'))
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        return ArticleTable::configure(
            $table->query(
                ArticleResource::getEloquentQuery()
                    ->where('status', ArticleStatus::PUBLISHED)
            )
        );
    }
}
