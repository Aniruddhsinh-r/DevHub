<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use Filament\Widgets\Widget;

class TopAuthors extends Widget
{
    protected string $view = 'filament.widgets.top-authors';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    public function getViewData(): array
    {
        $author = User::query()
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->first();

        return [
            'author' => $author,
            'comments' => Comment::count(),
            'likes' => Like::count(),
        ];
    }
}