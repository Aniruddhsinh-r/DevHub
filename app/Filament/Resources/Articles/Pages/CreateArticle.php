<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use App\Models\Article;
use App\Enums\ArticleStatus;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $base = Str::slug($data['title'], '-');
        $slug = $base;
        $count = 2;

        while (Article::where('slug', $slug)->withoutTrashed()->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }
        $data['slug'] = $slug;

        if (isset($data['status']) && $data['status'] === ArticleStatus::PUBLISHED) {
            $data['published_at'] = now();
        }
        return $data;
    }

}
