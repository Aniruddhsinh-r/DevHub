<?php

use App\Models\Article;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Enums\ArticleStatus;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Article::where('status', ArticleStatus::SCHEDULED)
        ->where('published_at', '<=', now())
        ->update(['status' => ArticleStatus::PUBLISHED, 'publish_at' => now(), 'published_at' => null]);
})->everyMinute();
