<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Article::where('status', ArticleStatus::SCHEDULED)
        ->where('duration', '<=', now())
        ->update(['status' => ArticleStatus::PUBLISHED, 'published_at' => now(), 'duration' => null]);
})->everyMinute();
