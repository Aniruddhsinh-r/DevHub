<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'two_factor_secret', 'last_seen_at', 'bio', 'avatar'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }
    public function views(): HasMany
    {
        return $this->hasMany(View::class); // Make sure you have a View model
    }

    // 2. Comments Relationship
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // 3. Bookmarks Relationship
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    // 4. Likes Relationship
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function bookmarkedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'bookmarks');
    }
}
