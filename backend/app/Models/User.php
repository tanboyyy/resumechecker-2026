<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function analyses(): HasManyThrough
    {
        return $this->hasManyThrough(Analysis::class, Resume::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function getPlan(): string
    {
        return $this->subscription?->plan ?? 'free';
    }

    public function isPro(): bool
    {
        return in_array($this->getPlan(), ['pro', 'enterprise']);
    }

    public function isEnterprise(): bool
    {
        return $this->getPlan() === 'enterprise';
    }

    public static function findOrCreateFromSocialite(SocialiteUser $socialiteUser): self
    {
        return static::updateOrCreate(
            ['google_id' => $socialiteUser->getId()],
            [
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'avatar' => $socialiteUser->getAvatar(),
                'provider' => 'google',
            ]
        );
    }
}
