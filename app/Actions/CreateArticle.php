<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Container\Attributes\CurrentUser;

class CreateArticle
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $values, ?User $user = null): void
    {
        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status',
        ])->toArray();

        $base = Str::slug($title, '-');
        $slug = $base;
        $count = 2;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        $data['slug'] = $slug;

        if ($values['cover_path'] ?? false) {
            $data['cover_path'] = $values['cover_path']->store('articleCovers','public');
        }

        if ($values['status'] === ArticleStatus::DRAFT) {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === ArticleStatus::PUBLISHED) {
            $data['published_at'] = now();
        } else {
            $data['published_at'] = null;
        }

        auth()->user()->articles()->create($data);
    }
}
