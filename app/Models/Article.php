<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, SoftDeletes;

    // These fields must match your migration and your Action data
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'cover_path',
        'duration',
        'view_count',
        'published_at',
    ];

    protected static function booted()
    {
        static::deleting(function (Article $article) {
            if ($article->isForceDeleting()) {
                $article->likes()->delete();
                Storage::disk('public')->delete($article->cover_path);
            } else {
                $article->comments()->delete();
                $article->bookmarks()->detach();
                $article->views()->delete();
            }
        });

        static::updated(function (Article $article) {
            if (
                $article->wasChanged('status') &&
                $article->getOriginal('status') === ArticleStatus::PUBLISHED &&
                in_array($article->status, [
                    ArticleStatus::DRAFT,
                    ArticleStatus::SCHEDULED,
                ])
            ) {
                $article->likes()->delete();
                $article->bookmarks()->detach();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isBookmarkedByMe()
    {
        return $this->bookmarks()->where('user_id', auth()->id())->first();
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks');
    }

    public function isLikedByUser(): bool
    {
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function topLevelComments(): HasMany
    {
        return $this->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'replies.replies.user', 'replies.replies.parent.user'])
            ->latest();
    }

    protected function casts(): array
    {
        return [
            'duration' => 'datetime',
            'published_at' => 'datetime',
            'status' => ArticleStatus::class,
        ];
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }
}
