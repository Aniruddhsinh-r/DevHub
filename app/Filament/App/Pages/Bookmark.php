<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;

class Bookmark extends Page
{
    protected string $view = 'components.articleLayout';

    protected static bool $shouldRegisterNavigation = false; 
    
    public string $search = '';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Article')
                ->icon('heroicon-o-plus')
                ->url('/articles/create')
                ->color('primary'),
        ];
    } 

    public function getArticlesProperty()
    {
        return auth()->user()
            ->bookmarkedArticles()
            ->when($this->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);
    }
}
