<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateArticle
{
    public function handle(array $values, Article $article): void
    {
        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id','status',
        ])->toArray();

        $data['slug'] = Str::slug($values['title'],'-');

        if ($values['cover_path'] ?? false) {
            $data['cover_path'] = $values['cover_path']->store('articleCovers','public');
        }

        if ($values['status'] === 'scheduled' && !empty($values['scheduled_minutes'])) {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === 'published') {
            $data['published_at'] = now();
        }

        DB::transaction(function () use ($article, $data) {
            $article->update($data);
        });
    }
}
