<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('all')->badge(Article::count()),
            'published' => Tab::make('published')->badge(Article::query()->where('status', ArticleStatus::PUBLISHED)->count())->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::PUBLISHED)),
            'draft' => Tab::make('drafts')->badge(Article::query()->where('status', ArticleStatus::DRAFT)->count())->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArticleStatus::DRAFT)),
        ];
    }
}
