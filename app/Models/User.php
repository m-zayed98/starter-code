<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AdvertiserType;
use App\Traits\HasOtps;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasOtps;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'birth_date',
        'identity_number',
        'verified_by_nafath',
        'status',
        'disabled_reason',
        'country_code',
        'disabled_at',

        // FAL / Real Estate Authority advertiser profile
        'fal_license_number',
        'nhc_mobile',
        'advertiser_type',
        'commercial_registration_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'birth_date' => 'date',
            'password' => 'hashed',
            'disabled_at' => 'datetime',
            'verified_by_nafath' => 'boolean',
            'advertiser_type' => AdvertiserType::class,
        ];
    }

    public function getAvatarAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        return $media?->getFullUrl();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();

        $this->addMediaCollection('commercial_registration')
            ->singleFile();
    }

    protected function hasPermissionViaRole(Permission $permission): bool
    {
        if (is_a($this, Role::class)) {
            return false;
        }

        return $this->hasRole(
            $permission->roles->filter(fn ($role) => $role->is_active)
        );
    }

    /******************************* Relationships **************/

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function nafathVerificationRequests(): HasMany
    {
        return $this->hasMany(NafathVerificationRequest::class);
    }

    /******************************* FCM Routing **************/

    /**
     * Route notifications for the FCM channel.
     * Returns all device tokens associated with this user.
     *
     * @return array<int, string>
     */
    public function routeNotificationForFcm(): array
    {
        return $this->getDeviceTokens();
    }

    /**
     * Get all FCM device tokens for this user.
     *
     * @return array<int, string>
     */
    public function getDeviceTokens(): array
    {
        return $this->fcmTokens()->pluck('token')->all();
    }

    /******************************* Attributes **************/
    public function fullPhone(): Attribute
    {
        return Attribute::get(
            fn () => $this->country_code.$this->phone,
        );
    }
}
