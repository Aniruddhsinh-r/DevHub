<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use App\Models\Article;
use Filament\Actions\Action;

class MyArticles extends Page
{
    protected string $view = 'components.articleLayout';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) Article::where('user_id', auth()->id())->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Article')
                ->icon('heroicon-o-plus')
                ->url('/articles/create')
                ->color('primary'),
        ];
    } 

    public function getArticlesProperty()
    {
        return Article::with(['user', 'category'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);
    }
}