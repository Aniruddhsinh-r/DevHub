<?php

namespace App\Filament\Resources\Articles\Widgets;

use App\Models\Article;
use App\Models\User;
use App\Models\Like;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'System Performance';
    protected ?string $description = 'Real-time overview of system stats.';
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

        $currentMonthLikes = Like::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthLikes = Like::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $likeDifference = $currentMonthLikes - $lastMonthLikes;

        $likePercent = $lastMonthLikes > 0
            ? round(($likeDifference / $lastMonthLikes) * 100)
            : ($currentMonthLikes > 0 ? 100 : 0);

        $likeIncrease = $likeDifference >= 0;

        $likeChart = collect(range(6, 0))
            ->map(fn ($daysAgo) => Like::whereDate(
                'created_at',
                Carbon::today()->subDays($daysAgo)
            )->count())
            ->toArray();

        return [
            Stat::make('Total Articles', $totalArticles)
                ->description($increaseText)
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData)
                ->color($diff >= 0 ? 'success' : 'danger'),
            Stat::make('New Users', $today)
                ->description(
                    $isIncrease
                        ? "{$percent}% increase (+{$difference} users) today"
                        : abs($percent) . "% decrease (" . $difference . " users) today"
                )
                ->descriptionIcon(
                    $isIncrease
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->chart([12, 15, 18, 10, 14, $yesterday, $today])
                ->color($isIncrease ? 'success' : 'danger'),
            Stat::make('Likes', $currentMonthLikes)
                ->description(
                    $likeIncrease
                        ? "{$likePercent}% increase (+{$likeDifference} likes) this month"
                        : abs($likePercent) . "% decrease ({$likeDifference} likes) this month"
                )
                ->descriptionIcon(
                    $likeIncrease
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->chart($likeChart)
                ->color($likeIncrease ? 'success' : 'danger')
        ];
    }
}
