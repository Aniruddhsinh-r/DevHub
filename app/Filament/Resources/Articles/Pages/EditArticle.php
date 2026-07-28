<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Support\Str;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($data['status'] === ArticleStatus::SCHEDULED) {
            $data['published_at'] = null;
        } elseif ($data['status'] === ArticleStatus::PUBLISHED) {
            $data['duration'] = null;
            $data['published_at'] = $data['published_at']->published_at ?? now();
        } else {
            $data['duration'] = null;
            $data['published_at'] = null;
        }

        if (! empty($data['title'])) {
            $base = Str::slug($data['title'], '-');
            $slug = $base;
            $count = 2;

            while (Article::where('slug', $slug)->where('id', '!=', $record->id)->withoutTrashed()->exists()) {
                $slug = $base . '-' . $count;
                $count++;
            }

            $data['slug'] = $slug;
        }

        $record->update($data);
        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
            ForceDeleteAction::make()->successNotificationTitle('Article permanently deleted.'),
            RestoreAction::make(),
        ];
    }
}
