<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasOtps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use InteractsWithMedia;
    use HasRoles;
    use HasOtps;

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
        'status',
        'disabled_reason',
        'country_code',
        'disabled_at',
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
        ];
    }

    public function getAvatarAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        return $media?->getFullUrl();
    }

    protected function hasPermissionViaRole(Permission $permission): bool
    {
        if (is_a($this, Role::class)) {
            return false;
        }

        return $this->hasRole(
            $permission->roles->filter(fn($role) => $role->is_active)
        );
    }


















    /******************************* Attributes **************/
    public function fullPhone(): Attribute
    {
        return Attribute::get(
            fn() => $this->country_code . $this->phone,
        );
    }
}
