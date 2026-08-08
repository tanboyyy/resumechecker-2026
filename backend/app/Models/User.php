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
        'stripe_customer_id',
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
        $attributes = [
            'name' => $socialiteUser->getName(),
            'avatar' => $socialiteUser->getAvatar(),
            'provider' => 'google',
        ];

        // Match on google_id first, then fall back to the verified email so an
        // account created before Google sign-in is linked rather than colliding
        // with the unique email constraint.
        $user = static::where('google_id', $socialiteUser->getId())->first()
            ?? static::where('email', $socialiteUser->getEmail())->first();

        if ($user) {
            $user->update($attributes + [
                'google_id' => $socialiteUser->getId(),
                'email' => $socialiteUser->getEmail(),
            ]);

            return $user;
        }

        return static::create($attributes + [
            'google_id' => $socialiteUser->getId(),
            'email' => $socialiteUser->getEmail(),
        ]);
    }
}
