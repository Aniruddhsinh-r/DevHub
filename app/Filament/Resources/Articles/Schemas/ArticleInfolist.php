<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use App\Enums\ArticleStatus;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                        ->schema([
                            Group::make([
                                TextEntry::make('title')
                                    ->size('lg')
                                    ->weight('bold'),
                                TextEntry::make('excerpt')
                                    ->color('gray')
                                    ->size('lg')
                                    ->extraAttributes(['class' => 'italic border-l-4 border-primary-400 pl-4'])
                                    ->columnSpanFull(),
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('status')
                                            ->size('lg')
                                            ->badge()
                                            ->color(fn (ArticleStatus $state): string => match ($state) {
                                                ArticleStatus::DRAFT => 'warning',
                                                ArticleStatus::PUBLISHED => 'success',
                                                ArticleStatus::SCHEDULED => 'gray',
                                            }),
                                        TextEntry::make('category.name')
                                            ->label('Category')
                                            ->size('lg')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('view_count')
                                            ->label('Views')
                                            ->numeric()
                                            ->icon('heroicon-m-eye'),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ]),
                            ImageEntry::make('cover_path')
                                ->disk('public')
                                ->hiddenLabel()
                                ->maxWidth('300px')
                                ->height('180px')
                                ->extraImgAttributes(['class' => 'w-full h-full object-cover rounded-lg'])
                                ->placeholder('No cover image')
                                ->columnSpan([
                                    'default' => 1,
                                    'lg' => 1,
                                ]),
                        ]),
                        TextEntry::make('body')
                            ->label('Content')
                            ->size('lg'),
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('updated_at')
                                ->label('Updated At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('published_at')
                                ->label('Published At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('Not published yet'),

                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->dateTime('d M Y, H:i')
                                ->color('danger')
                                ->visible(fn (Article $record): bool => $record->trashed()),
                        ])
                        ->columnSpanFull(),
                    ]),
            ]);
    }
}
