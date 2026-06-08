<?php

namespace App\Providers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    protected $policies = [
        Article::class => ArticlePolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
    }
}
