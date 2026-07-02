<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Article;
use App\Models\User;
use App\Events\ArticleCreate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Attributes\CurrentUser;

class ArticleDelete
{
    public function __construct(#[CurrentUser] protected User $user) {}

    public function handle(Article $article): void
    {
        if ($article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }
        $article->likes()->delete();
        $article->comments()->delete();
        $article->views()->delete();
        $article->bookmarks()->detach();
        $article->delete();

        ArticleCreate::dispatch();
    }
}