<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /** @use HasFactory<CategoryFactory> */
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
