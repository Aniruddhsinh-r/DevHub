<?php

namespace App\Filament\App\Resources\Users\Schemas;

use App\Enums\ArticleStatus;
use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Schema;

class UserInfolist
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
                            'lg' => 4,
                        ])
                        ->schema([
                            ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->circular()
                                ->height('120px')
                                ->extraImgAttributes([
                                    'class' => 'object-cover',
                                ])
                                ->placeholder('No avatar')
                                ->columnSpan([
                                    'default' => 1,
                                    'lg' => 1,
                                ]),
                            Group::make([
                                TextEntry::make('name')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpanFull(),
                                Actions::make([
                                    Action::make('follow')
                                        ->label(fn (User $record) => auth()->user()->following()->whereKey($record)->exists()
                                            ? 'Unfollow'
                                            : 'Follow')
                                        ->icon(fn (User $record) => auth()->user()->following()->whereKey($record)->exists()
                                            ? 'heroicon-o-user-minus'
                                            : 'heroicon-o-user-plus')
                                        ->color(fn (User $record) => auth()->user()->following()->whereKey($record)->exists()
                                            ? 'gray'
                                            : 'primary')
                                        ->hidden(fn (User $record) => auth()->id() === $record->id)
                                        ->action(function (User $record) {
                                            $user = auth()->user();

                                            if ($user->following()->whereKey($record)->exists()) {
                                                $user->following()->detach($record);
                                            } else {
                                                $user->following()->attach($record);
                                            }
                                        }),
                                ]),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ]),
                        ]),
                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Email copied')
                            ->columnSpanFull(),
                        TextEntry::make('bio')
                            ->label('Bio')
                            ->placeholder('No bio added')
                            ->prose()
                            ->columnSpanFull(),
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Member since')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),
                            TextEntry::make('article_count')
                                ->state(fn ($record) => $record->articles()->where('status', ArticleStatus::PUBLISHED)->count())
                                ->label('Articles')
                                ->badge(),
                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->dateTime('d M Y, H:i')
                                ->color('danger')
                                ->visible(fn (User $record): bool => $record->trashed()),
                        ])
                        ->columnSpanFull(),
                    ]),
            ]);
    }
}
