<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('user_id')
                    ->content(fn ($record) => $record?->user?->name ?? auth()->user()->name)
                    ->disabled()
                    ->dehydrated(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(Article::class, 'slug', ignoreRecord: true),
                TextInput::make('excerpt')
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(ArticleStatus::class)
                    ->default(ArticleStatus::DRAFT)
                    ->live()
                    ->required(),
                DateTimePicker::make('duration')
                    ->visible(fn ($get) => $get('status') === ArticleStatus::SCHEDULED)
                    ->required(fn ($get) => $get('status') === ArticleStatus::SCHEDULED),
                DateTimePicker::make('published_at')
                    ->visible(fn ($get) => $get('status') === ArticleStatus::PUBLISHED)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('cover_path')
                    ->disk('s3')
                    ->directory('articleCovers')
                    ->visibility('public')
            ]);
    }
}
