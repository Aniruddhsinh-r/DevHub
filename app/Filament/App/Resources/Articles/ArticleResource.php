<?php

namespace App\Filament\App\Resources\Articles;

use App\Filament\App\Resources\Articles\Pages\Bookmark;
use App\Filament\App\Resources\Articles\Pages\CreateArticle;
use App\Filament\App\Resources\Articles\Pages\EditArticle;
use App\Filament\App\Resources\Articles\Pages\ListArticles;
use App\Filament\App\Resources\Articles\Pages\MyArticles;
use App\Filament\App\Resources\Articles\Pages\ViewArticle;
use App\Filament\App\Resources\Articles\Schemas\ArticleForm;
use App\Filament\App\Resources\Articles\Schemas\ArticleInfolist;
use App\Models\Article;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArticleResource extends Resource
{
    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $model = Article::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticleInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'bookmarks' => Bookmark::route('/bookmarks'),
            'my-articles' => MyArticles::route('/my-articles'),
            'view' => ViewArticle::route('/{record}'),
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
