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
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->icon('heroicon-o-users')
                ->color('primary'),
                
            Stat::make('Total Articles', Article::count())
                ->description('Published content')
                ->icon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Total Likes', Like::count())
                ->description('Community engagement')
                ->icon('heroicon-o-heart')
                ->color('danger'),
        ];
    }
}
