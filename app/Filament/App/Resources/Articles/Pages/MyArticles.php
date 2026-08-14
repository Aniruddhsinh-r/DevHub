<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use App\Filament\App\Resources\Articles\Tables\ArticleTable;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class MyArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected static ?string $title = 'My Articles';

    protected function getHeaderActions(): array
    {
        return [
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
                    ->where('user_id', auth()->id())
            )
        );
    }
}
