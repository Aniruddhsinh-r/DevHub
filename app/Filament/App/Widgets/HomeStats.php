<?php

namespace App\Filament\App\Widgets;

use App\Models\Article;
use App\Models\Like;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HomeStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Views', number_format(auth()->user()->articles()->withCount('views')->get()->sum('views_count')))
                ->description('Views across all your articles')
                ->icon('heroicon-o-eye')
                ->color('primary'),

            Stat::make('Total Articles', number_format(auth()->user()->articles()->count()))
                ->description('Articles you have created')
                ->icon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Total Likes', number_format(auth()->user()->articles()->withCount('likes')->get()->sum('likes_count')))
                ->description('Likes received from readers')
                ->icon('heroicon-o-heart')
                ->color('danger'),

            Stat::make('Followers', number_format(auth()->user()->followers()->count()))
                ->description('People following you')
                ->icon('heroicon-o-users')
                ->color('success'),
        ];
    }
}
