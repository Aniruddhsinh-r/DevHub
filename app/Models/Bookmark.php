<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function bookmark()
    {
        return $this->belongsToMany(User::class);
    }
}
