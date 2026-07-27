<?php

namespace App\Filament\App\Resources\Articles\Schemas;
 
use App\Enums\ArticleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\Article;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Details')
                            ->columns(2)
                            ->schema([
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('status')
                                    ->options(ArticleStatus::class)
                                    ->default(ArticleStatus::DRAFT)
                                    ->live()
                                    ->required(),
                                TextInput::make('title')
                                    ->required()
                                    ->live()
                                    ->minLength(6)
                                    ->maxLength(50)
                                    ->columnSpanFull()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Article::class, 'slug', ignoreRecord: true)
                                    ->columnSpanFull(),
                                TextInput::make('excerpt')
                                    ->required()
                                    ->minLength(20)
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                DateTimePicker::make('duration')
                                    ->visible(fn ($get) => $get('status') === ArticleStatus::SCHEDULED)
                                    ->required(fn ($get) => $get('status') === ArticleStatus::SCHEDULED),
                            ]),
                            Section::make('Content')
                            ->schema([
                                Textarea::make('body')
                                    ->required()
                                    ->rows(12)
                                    ->maxLength(50000),
                            ]),
            ]);
    }
}
