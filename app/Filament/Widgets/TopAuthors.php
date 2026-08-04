<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Models\Comment;
use App\Models\Bookmark;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopAuthors extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $author = User::query()
            ->withCount([
                'articles',
                'comments',
                'likes',
            ])
            ->orderByDesc('articles_count')
            ->first();

        if (! $author) {
            return [
                Stat::make('Top Author', 'No authors'),
            ];
        }

        return [
            Stat::make('Top Author', $author->name)
                ->description('View profile')
                ->url(
                    UserResource::getUrl('view', [
                        'record' => $author->uuid,
                    ])
                ),

            Stat::make(
                'Totle Comments',
                Comment::count()
            ),

            Stat::make(
                'Totle Likes',
                Bookmark::count()
            ),
        ];
    }
}
