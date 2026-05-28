<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function workWith(User $user, Article $article)
    {
        return $article->user->is($user);
    }

    public function create(User $user): bool {
        return $user->role === 'author';
    }

    public function update(User $user, Article $article): bool {
        return $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool {
        return $user->id === $article->user_id || $user->role === 'admin';
    }
}
