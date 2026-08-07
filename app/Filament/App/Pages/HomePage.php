<?php

namespace App\Filament\App\Pages;

use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Filament\App\Widgets\HomeStats;
use App\Filament\App\Widgets\TopArticles;

class HomePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'home';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Home';

    protected function getHeaderWidgets(): array
    {
        return [
            HomeStats::class,
            TopArticles::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('myarticle')
                ->label('My Articles')
                ->url('/articles/my-articles')
                ->color('primary'),
        ];
    }
}
