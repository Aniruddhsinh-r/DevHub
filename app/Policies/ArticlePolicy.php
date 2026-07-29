<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\ArticleStatus;

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
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }

        return $user->can('article.edit') && $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool {
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            return true;
        }
        return $user->can('article.delete') && $user->id === $article->user_id;
    }

    public function publish(User $user): bool
    {
        return $user->can('article.publish');
    }

    public function like(User $user, Article $article): bool
    {
        return $user->hasRole(UserRole::AUTHOR) && $article->status === ArticleStatus::PUBLISHED;
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]) || $article->status === ArticleStatus::PUBLISHED || $article->user_id === $user->id;
    }

    public function bookmark(User $user, Article $article): bool
    {
        return $user->hasRole(UserRole::AUTHOR) && $article->status === ArticleStatus::PUBLISHED;
    }
}
