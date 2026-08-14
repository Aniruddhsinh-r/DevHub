<?php

namespace App\Filament\App\Resources\Articles\Tables;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['views']))
            ->contentGrid(['md' => 2])
            ->columns([
                Stack::make([
                    Stack::make([
                        ImageColumn::make('cover_path')
                            ->disk('public')
                            ->height('160px')
                            ->extraImgAttributes(['class' => 'w-full object-cover rounded-xl'])
                            ->defaultImageUrl('https://media.licdn.com/dms/image/v2/C5112AQHyTivjkijUAg/article-cover_image-shrink_720_1280/article-cover_image-shrink_720_1280/0/1533804257780?e=2147483647&v=beta&t=iHBq7iyRl4h07KSszls8TpCujE45XPFMkyqgt5Z-FA8'),
                        TextColumn::make('views_count')
                            ->numeric()
                            ->badge()
                            ->suffix(' Views')
                            ->color('gray')
                            ->extraAttributes(['class' => 'absolute top-2 left-2 z-10']),
                    ])->extraAttributes(['class' => 'relative']),
                    Split::make([
                        ImageColumn::make('user.avatar')
                            ->circular()
                            ->height(28)
                            ->defaultImageUrl(
                                fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->user->name)
                            ),
                        TextColumn::make('user.name')
                            ->weight('semibold')
                            ->grow()
                            ->searchable(),
                        TextColumn::make('category.name')
                            ->badge()
                            ->color('gray'),
                    ])->from('md'),
                    TextColumn::make('title')
                        ->weight('bold')
                        ->size('lg')
                        ->limit(50)
                        ->searchable(),
                    TextColumn::make('excerpt')
                        ->color('gray')
                        ->limit(100)
                        ->searchable(),
                    Split::make([
                            TextColumn::make('created_at')
                            ->since()
                            ->color('gray')
                            ->size('xs'),
                            TextColumn::make('published_at')
                            ->date('M d, Y')
                            ->color('gray')
                            ->size('xs')
                            ->alignEnd(),
                        ]),
                ])->space(3),
            ])
            ->recordUrl(fn ($record) => ArticleResource::getUrl('view', [
                    'record' => $record,
                ]))
            ->searchable()
            ->paginated([12, 24, 48]);
    }
}
