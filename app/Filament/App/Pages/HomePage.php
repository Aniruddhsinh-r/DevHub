<?php

namespace App\Filament\App\Pages;

use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Pages\Page;
use App\Filament\App\Widgets\HomeStats;
use App\Filament\App\Widgets\TopArticles;

class HomePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'home';

    protected function getHeaderWidgets(): array
    {
        return [
            HomeStats::class,
            TopArticles::class,
        ];
    }
}
