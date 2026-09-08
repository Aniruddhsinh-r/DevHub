<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
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

    public function create(User $user): bool
    {
        return $user->can('article.create');
    }

    public function update(User $user, Article $article): bool
    {
        if ($article->trashed()) {
            return false;
        }

        if ($user->hasRole(UserRole::SUPERADMIN) || $user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $user->can('article.edit') && $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->hasRole(UserRole::SUPERADMIN) || $user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        return $user->can('article.delete') && $user->id === $article->user_id;
    }

    public function like(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('article.like') && $article->status === ArticleStatus::PUBLISHED && $article->user_id !== $user->id;
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERADMIN]) || $article->status === ArticleStatus::PUBLISHED || $article->user_id === $user->id;
    }

    public function bookmark(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('article.bookmark') && $article->status === ArticleStatus::PUBLISHED && $article->user_id !== $user->id;
    }

    public function comment(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('article.comment') && $article->status === ArticleStatus::PUBLISHED;
    }

    public function forceDelete(User $user, Article $article): bool
    {
        if (! $article->trashed()) {
            abort(422, 'The article must be soft deleted before permanent deletion.');
        }

        return $user->hasPermissionTo('article.forceDelete');
    }
}
