<?php

namespace App\Filament\App\Widgets;

use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\Attributes\Computed;
use Filament\Widgets\Widget;

class TopArticles extends Widget
{
    protected string $view = 'components.articleCard';
    
    protected int|string|array $columnSpan = 'full';

    #[Computed]
    public function articles()
    {
        return Article::query()
            ->with(['user', 'category'])
            ->withCount('likes')
            ->where('status', ArticleStatus::PUBLISHED)
            ->orderByDesc('view_count')
            ->take(6)
            ->get();
    }
}