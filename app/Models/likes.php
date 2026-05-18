<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class likes extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function likes()
    {
        return $this->belongsToMany(User::class);
    }
}
