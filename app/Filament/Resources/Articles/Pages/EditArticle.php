<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]) ?? $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($data['status'] === ArticleStatus::SCHEDULED) {
            $data['published_at'] = null;
        } elseif ($data['status'] === ArticleStatus::PUBLISHED) {
            $data['duration'] = null;
            $data['published_at'] = $record->published_at ?? now();
        } else {
            $data['duration'] = null;
            $data['published_at'] = null;
        }

        if (! empty($data['title'])) {
            $base = Str::slug($data['title'], '-');
            $slug = $base;
            $count = 2;

            while (Article::where('slug', $slug)->where('id', '!=', $record->id)->withoutTrashed()->exists()) {
                $slug = $base.'-'.$count;
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
