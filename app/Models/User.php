<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'two_factor_secret', 'last_seen_at', 'bio', 'avatar'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'app') {
            return true;
        }

        return $this->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPERADMIN->value]);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
        ];
    }

    protected static function booted()
    {
        static::deleting(function (User $user) {
            if ($user->isForceDeleting()) {
                $user->articles()->withTrashed()->forceDelete();
                $user->comments()->withTrashed()->forceDelete();
                $user->likes()->forceDelete();
            } else {
                $user->invitations()->delete();
                $user->views()->delete();
                $user->comments()->delete();
                $user->bookmarks()->delete();
                $user->articles()->delete();
                $user->followers()->detach();
                $user->following()->detach();
                $user->likes()->delete();
            }
        });

        static::restored(function (User $user) {
            $user->articles()->onlyTrashed()->restore();
            $user->comments()->onlyTrashed()->restore();
        });

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
        return $this->hasMany(View::class);
    }

    // 2. Comments Relationship
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
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

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'email','email');
    }

    public function bookmarkedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'bookmarks');
    }
}
