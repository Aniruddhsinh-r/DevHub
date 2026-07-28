<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;
    
    protected string $view = 'components.filament.view-article';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->successNotificationTitle('Article edited successfully.'),
            DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
        ];
    }
}
