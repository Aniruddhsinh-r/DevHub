<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class likes extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function likes()
    {
        return $this->belongsToMany(User::class);
    }
}
