<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

use App\Models\Article;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;


class ArticleLikes extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.app.pages.article-likes';

    public Article $article;

    public function mount(Article $article): void
    {
        $this->article = $article;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->article
                    ->likes()
                    ->with('user')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Liked At')
                    ->since(),
            ]);
    }
}
