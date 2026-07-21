<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
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
        'published_at'
    ];

    protected static function booted()
    {
        static::deleting(function (Article $article) {
            if ($article->isForceDeleting()) {
                $article->likes()->delete();
                $article->views()->delete();
                $article->comments()->delete();
                $article->bookmarks()->detach();
            } else {
                $article->comments()->delete();
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
        return $this->hasMany(Comment::class);
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
    protected function casts(): array
    {
        return [
            'duration' => 'datetime',
            'published_at' => 'datetime',
            'status' => ArticleStatus::class,
        ];
    }
    public function views() { return $this->hasMany(View::class); }
}
