<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Enums\ArticleStatus;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'articles';

    public function isReadOnly(): bool
    {
        return false;
    }

    protected static ?string $relatedResource = ArticleResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('5xl')
                    ->url('')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->form([
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
                                    ->required(fn ($get) => $get('status') === ArticleStatus::SCHEDULED)
                                    ->minDate(now())
                                    ->maxDate(now()->addHours(48))
                                    ->rules(['after_or_equal:now', 'before_or_equal:+48 hours']),
                            ]),
                        Section::make('Content')
                            ->schema([
                                Textarea::make('body')
                                    ->required()
                                    ->rows(12)
                                    ->maxLength(50000),
                            ]),
                    ]),
            ])
            ->emptyStateHeading('No article has been posted by this user yet.');
    }
}