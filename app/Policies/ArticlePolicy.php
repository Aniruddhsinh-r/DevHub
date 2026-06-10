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
        return $user->can('article.create');
    }

    public function update(User $user, Article $article): bool {
        return $user->can('article.edit') && $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool {
        return $user->can('article.delete') && $user->id === $article->user_id;
    }

    public function publish(User $user): bool
    {
        return $user->can('article.publish');
    }
}
