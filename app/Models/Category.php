<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function (Category $category) {
            $category->articles()->delete();
        });
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
