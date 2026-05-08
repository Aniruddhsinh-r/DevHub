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
        'cover_path'
    ];

    /**
     * Relationship: An article belongs to a User (Author)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
