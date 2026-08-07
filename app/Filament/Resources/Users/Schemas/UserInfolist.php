<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
                                ->disk('public')
                                ->circular()
                                ->imageWidth(120)
                                ->defaultImageUrl('https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_1280.png')
                                ->extraImgAttributes([
                                    'class' => 'object-cover',
                                ])
                                ->columnSpan([
                                    'default' => 1,
                                    'lg' => 1,
                                ]),
                            Group::make([
                                TextEntry::make('name')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpanFull(),

                                TextEntry::make('email')
                                    ->icon('heroicon-o-envelope')
                                    ->color('gray')
                                    ->copyable()
                                    ->copyMessage('Email copied')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ]),
                        ]),
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
                                ->label('Created At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),
                            TextEntry::make('updated_at')
                                ->label('Updated At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),
                            TextEntry::make('email_verified_at')
                                ->label('Email Verified')
                                ->badge()
                                ->dateTime('d M Y, H:i')
                                ->color(fn ($state) => $state ? 'success' : 'danger')
                                ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('d M Y, H:i') : 'Not verified')
                                ->icon(fn ($state) => $state ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle')
                                ->placeholder('Not verified'),
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
