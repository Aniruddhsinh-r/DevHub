<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TrashedFilter;
use App\Enums\ArticleStatus;
use Filament\Actions\RestoreAction; 
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_path')
                    ->label('Image')
                    ->disk('public')
                    ->imageWidth(80),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ArticleStatus $state): string => match ($state) {
                        ArticleStatus::DRAFT => 'warning',
                        ArticleStatus::PUBLISHED => 'success',
                        ArticleStatus::SCHEDULED => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->successNotificationTitle('Article edited successfully.'),
                DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->successNotificationTitle('Articles deleted successfully.'),
                    ForceDeleteBulkAction::make()->successNotificationTitle('Articles permanently deleted.'),
                    RestoreBulkAction::make(),
                ]),
            ])->emptyStateHeading(function ($livewire) { $activityTab = $livewire->activeTab ?? $livewire->activityTab ?? 'all';
                    return match ($activityTab) {
                        'all'       => 'No articles have been created yet.',
                        'published' => 'No published articles found.',
                        'draft'     => 'No draft articles found.',
                        'scheduled' => 'No scheduled articles found.',
                        default     => 'No articles found.',
                    };
                })
                ->searchPlaceholder('Search articles');
    }
}
