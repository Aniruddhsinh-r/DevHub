<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;
    
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('components.filament.view-article'),
            $this->getRelationManagersContentComponent(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->successNotificationTitle('Article edited successfully.'),
            DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
        ];
    }
}