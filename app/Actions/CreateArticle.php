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
        $user = Auth::user();

        $title = $values['title'];
        $slug = Str::slug($title,'-');

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id','status',
        ])->toArray();

        $data['slug'] = $slug;
        if ($values['cover_path'] ?? false) {
            $data['cover_path'] = $values['cover_path']->store('articleCovers','public');
        }

        // dd(['all' => $data]);

        DB::transaction(function () use ($data, $values) {
            $this->user->articles()->create($data);
        });
    }
}
