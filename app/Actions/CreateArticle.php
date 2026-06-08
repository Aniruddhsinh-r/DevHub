<?php

declare(strict_types=1);

namespace App\Actions;

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
        unset($values['_token']);

        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id','status',
        ])->toArray();

        $data['slug'] = Str::slug($title,'-');

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
