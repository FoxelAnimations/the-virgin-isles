<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_blocked',
        'blocked_until',
        'block_reason',
        'blocked_by',
        'is_comment_blocked',
        'comment_blocked_until',
        'comment_block_reason',
        'comment_blocked_by',
        'last_active_at',
        'news_dismissed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

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
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_until' => 'datetime',
            'is_comment_blocked' => 'boolean',
            'comment_blocked_until' => 'datetime',
            'last_active_at' => 'datetime',
            'news_dismissed_at' => 'datetime',
        ];
    }

    public function collectedBeacons(): BelongsToMany
    {
        return $this->belongsToMany(Beacon::class, 'beacon_user')
            ->withPivot('collected_at')
            ->withCasts(['collected_at' => 'datetime']);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
            ->withPivot('count', 'collected_at', 'updated_at')
            ->withCasts(['collected_at' => 'datetime', 'updated_at' => 'datetime']);
    }

    public function revealedLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_user')
            ->withPivot('revealed_at')
            ->withCasts(['revealed_at' => 'datetime']);
    }

    public function isAccountBlocked(): bool
    {
        return $this->is_blocked
            && ($this->blocked_until === null || $this->blocked_until->isFuture());
    }

    public function isCommentBlocked(): bool
    {
        return $this->is_comment_blocked
            && ($this->comment_blocked_until === null || $this->comment_blocked_until->isFuture());
    }

    public function accountBlockLabel(): ?string
    {
        if (! $this->isAccountBlocked()) {
            return null;
        }

        return $this->blocked_until
            ? 'Blocked (' . $this->blocked_until->diffForHumans() . ')'
            : 'Blocked (permanent)';
    }

    public function commentBlockLabel(): ?string
    {
        if (! $this->isCommentBlocked()) {
            return null;
        }

        return $this->comment_blocked_until
            ? 'Comment blocked (' . $this->comment_blocked_until->diffForHumans() . ')'
            : 'Comment blocked (permanent)';
    }

    public function blockedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function commentBlockedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'comment_blocked_by');
    }
}
