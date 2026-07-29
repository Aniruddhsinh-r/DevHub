<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use App\Models\Article;

class MyArticles extends Page
{
    protected string $view = 'components.articleLayout';

    public static function getNavigationBadge(): ?string
    {
        return (string) Article::where('user_id', auth()->id())->count();
    }

    public function getArticlesProperty()
    {
        return Article::with(['user', 'category'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);
    }
}