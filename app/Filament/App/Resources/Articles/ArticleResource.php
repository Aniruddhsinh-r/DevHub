<?php

namespace App\Filament\App\Resources\Articles;

use App\Filament\App\Resources\Articles\Pages\CreateArticle;
use App\Filament\App\Resources\Articles\Pages\EditArticle;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
use App\Filament\App\Resources\Articles\Pages\ListArticles;
use App\Filament\App\Resources\Articles\Schemas\ArticleForm;
use App\Models\Article;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArticleResource extends Resource
{
    protected static ?string $navigationLabel = 'Articles';
    
    protected static ?string $model = Article::class;

    protected static ?string $recordTitleAttribute = 'title';
        
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
 
    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view'   => ViewArticle::route('/{record}'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
