<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

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
        'published_at'
    ];

    /**
     * Relationship: An article belongs to a User (Author)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): BelongsTo
    {
        return $this->belongsTo(likes::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookmarked()
    {
        return $this->belongsToMany(User::class, 'bookmarks')->where('user_id', auth()->id())->exists();
    }

    public function bookmarks()
{
    return $this->belongsToMany(User::class, 'bookmarks');
}
}
