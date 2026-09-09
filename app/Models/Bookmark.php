<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\BookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function bookmarks(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
