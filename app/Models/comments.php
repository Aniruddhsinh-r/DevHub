<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class comments extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'article_id',
        'parent_id',
        'body',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment() {
        return $this->hasMany(comments::class);
    }

    /**
     * Get the article that the comment belongs to.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the parent comment if this comment is a reply.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(comments::class, 'parent_id');
    }

    /**
     * Get all replies nested directly under this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(comments::class, 'parent_id');
    }
}
