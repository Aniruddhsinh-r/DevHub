<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Author')
                    ->default(auth()->id())
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit')
                    ->dehydrated()
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->minLength(6)
                    ->maxLength(50),
                TextInput::make('excerpt')
                    ->required()
                    ->minLength(20)
                    ->maxLength(255),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->minLength(30)
                    ->maxLength(50000),
                Select::make('status')
                    ->options(ArticleStatus::class)
                    ->default(ArticleStatus::DRAFT)
                    ->live()
                    ->required(),
                DateTimePicker::make('duration')
                    ->visible(fn ($get) => $get('status') === ArticleStatus::SCHEDULED)
                    ->required(fn ($get) => $get('status') === ArticleStatus::SCHEDULED)
                    ->minDate(now())
                    ->maxDate(now()->addHours(48))
                    ->rules(['after_or_equal:now', 'before_or_equal:+48 hours']),
                DateTimePicker::make('published_at')
                    ->visible(fn ($get) => $get('status') === ArticleStatus::PUBLISHED)
                    ->default(now())
                    ->dehydrated(),
                FileUpload::make('cover_path')
                    ->disk('public')
                    ->directory('articleCovers')
                    ->visibility('public')
                    ->image(),
            ]);
    }
}
