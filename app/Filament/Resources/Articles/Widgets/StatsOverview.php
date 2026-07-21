<?php

namespace App\Filament\Resources\Articles\Widgets;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Article Performance';
    protected ?string $description = 'Real-time overview of article publishing stats.';
    protected static bool $isLazy = false; 
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $totalArticles = Article::count();
        
        $articlesThisMonth = Article::where('created_at', '>=', now()->startOfMonth())->count();
        $articlesLastMonth = Article::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->count();

        $diff = $articlesThisMonth - $articlesLastMonth;
        $increaseText = ($diff >= 0 ? "+{$diff}" : "{$diff}") . ' from last month';

        // 3. Generate dynamic chart data for the last 7 days
        $chartData = collect(range(6, 0))->map(function ($daysAgo) {
            return Article::whereDate('created_at', Carbon::today()->subDays($daysAgo))->count();
        })->toArray();

        $today = User::whereDate('created_at', today())->count();
        $yesterday = User::whereDate('created_at', today()->subDay())->count();

        $difference = $today - $yesterday;
        $percent = $yesterday > 0 ? round(($difference / $yesterday) * 100) : 100;
        $isIncrease = $difference >= 0;

        return [
            Stat::make('Total Articles', $totalArticles)
                ->description($increaseText)
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData)
                ->color($diff >= 0 ? 'success' : 'danger'),
            Stat::make('New Users', $today)
    ->description(
        $isIncrease
            ? "{$percent}% increase (+{$difference} users)"
            : abs($percent) . "% decrease (" . $difference . " users)"
    )
    ->descriptionIcon(
        $isIncrease
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down'
    )
    ->chart([12, 15, 18, 10, 14, $yesterday, $today])
    ->color($isIncrease ? 'success' : 'danger'),
            Stat::make('Drafts', 15)
            ->description('3% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        ];
    }
}
