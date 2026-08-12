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

        $start = now()->subDays(6)->startOfDay();

        $counts = Article::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        $chartData = collect(range(6, 0))
            ->map(fn (int $daysAgo) =>
                $counts->get(
                    Carbon::today()->subDays($daysAgo)->toDateString(),0
                )
            )
            ->all();

        return [
            Stat::make('Total Articles', $totalArticles)
                ->description($increaseText)
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($chartData)
                ->color($diff >= 0 ? 'success' : 'danger'),
        
        ];
    }
}
