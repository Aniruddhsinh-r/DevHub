<?php

namespace App\Filament\App\Resources\Articles\RelationManagers;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LikesRelationManager extends RelationManager
{
    protected static string $relationship = 'likes';

    protected static ?string $relatedResource = ArticleResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
