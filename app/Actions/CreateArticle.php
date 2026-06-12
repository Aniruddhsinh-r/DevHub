<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateArticle
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $values, ?User $user = null): void
    {
        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status',
        ])->merge(['published_at' => now()])->toArray();

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

        if ($values['status'] === 'scheduled' && !empty($values['scheduled_minutes'])) {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === 'published') {
            $data['published_at'] = now();
        }

        DB::transaction(function () use ($data) {
            $this->user->articles()->create($data);
        });
    }
}
