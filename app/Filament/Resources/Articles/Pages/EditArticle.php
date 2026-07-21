<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
            ForceDeleteAction::make()->successNotificationTitle('Article permanently deleted.'),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // return $this->getResource()::getUrl('index');
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
