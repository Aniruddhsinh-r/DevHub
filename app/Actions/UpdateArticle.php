<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Article;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateArticle
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(array $values, Article $article): void
    {
        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id','status',
        ])->toArray();

        if (($values['title'] ?? null) && $values['title'] !== $article->title) {
            $base = Str::slug($values['title'], '-');
            $slug = $base;
            $count = 2;

            while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $base . '-' . $count;
                $count++;
            }

            $data['slug'] = $slug;
        }

        if ($this->hasFile($values, 'cover_path')) {
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }
            $data['cover_path'] = $values['cover_path']->store('articleCovers', 'public');
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
    /** Helper to check for valid file */
    private function hasFile(array $values, string $key): bool
    {
        return isset($values[$key]) && $values[$key] instanceof \Illuminate\Http\UploadedFile;
    }
}
