<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Article;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
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

        return [
            Stat::make('Total Articles', $totalArticles)
                ->description($increaseText)
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData)
                ->color($diff >= 0 ? 'success' : 'danger'),
        
        ];
    }
}
