<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use App\Enums\ArticleStatus;
use Filament\Actions\Action;
use App\Models\Article;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected string $view = 'components.articleLayout';

    public string $search = '';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Article')
                ->icon('heroicon-o-plus')
                ->url('/app/articles/create')
                ->color('primary'),
        ];
    }

    public function getArticlesProperty()
    {
        return Article::with(['user', 'category'])
            ->latest()
            ->where('status', ArticleStatus::PUBLISHED)
            ->when($this->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->paginate(12);
    }
}
