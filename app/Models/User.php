<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'business_profile_limit',
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
        'business_profile_limit' => 'integer',
    ];

    public function businessProfiles(): HasMany
    {
        return $this->hasMany(BusinessProfile::class);
    }

    public function accessibleBusinessProfiles(): BelongsToMany
    {
        return $this->belongsToMany(BusinessProfile::class, 'business_profile_user')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function canCreateBusinessProfile(): bool
    {
        return $this->businessProfiles()->count() < $this->business_profile_limit;
    }

    public function getRemainingBusinessProfiles(): int
    {
        return max(0, $this->business_profile_limit - $this->businessProfiles()->count());
    }

    public function hasBusinessProfileAccess(int $businessProfileId, string $permission = null): bool
    {
        // Check if user owns the business profile
        if ($this->businessProfiles()->where('business_profiles.id', $businessProfileId)->exists()) {
            return true;
        }

        // Check if user has shared access
        $access = $this->accessibleBusinessProfiles()
            ->where('business_profiles.id', $businessProfileId)
            ->first();

        if (!$access || !$access->pivot->is_active) {
            return false;
        }

        // If no specific permission required, just check access
        if (!$permission) {
            return true;
        }

        // Check specific permission
        $permissions = $access->pivot->permissions ? json_decode($access->pivot->permissions, true) : [];
        return in_array($permission, $permissions);
    }

    public function getAccessibleBusinessProfileIds(): array
    {
        $ownedIds = $this->businessProfiles()->pluck('business_profiles.id')->toArray();
        $sharedIds = $this->accessibleBusinessProfiles()->pluck('business_profiles.id')->toArray();
        
        return array_unique(array_merge($ownedIds, $sharedIds));
    }
}