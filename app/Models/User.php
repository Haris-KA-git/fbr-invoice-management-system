<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function businessProfiles(): HasMany
    {
        return $this->hasMany(BusinessProfile::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function accessibleBusinessProfiles(): BelongsToMany
    {
        return $this->belongsToMany(BusinessProfile::class)
            ->withPivot(['role', 'permissions', 'is_active'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function allBusinessProfiles(): BelongsToMany
    {
        return $this->belongsToMany(BusinessProfile::class)
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function hasBusinessProfileAccess($businessProfileId, $permission = null): bool
    {
        $profile = $this->accessibleBusinessProfiles()
            ->where('business_profile_id', $businessProfileId)
            ->first();

        if (!$profile) {
            return false;
        }

        if (!$permission) {
            return true;
        }

        $userPermissions = $profile->pivot->permissions ? json_decode($profile->pivot->permissions, true) : [];
        return in_array($permission, $userPermissions) || $profile->pivot->role === 'owner';
    }
}