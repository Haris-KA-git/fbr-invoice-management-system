<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'business_profile_limit',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        return $this->belongsToMany(BusinessProfile::class)
            ->withPivot(['role', 'permissions', 'is_active'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    public function allAccessibleBusinessProfiles()
    {
        $owned = $this->businessProfiles;
        $shared = $this->accessibleBusinessProfiles;
        
        return $owned->merge($shared)->unique('id');
    }

    public function hasBusinessProfileAccess($businessProfileId, $permission = null)
    {
        // Check if user owns the business profile
        if ($this->businessProfiles()->where('id', $businessProfileId)->exists()) {
            return true;
        }

        // Check if user has shared access
        $access = $this->accessibleBusinessProfiles()
            ->where('business_profiles.id', $businessProfileId)
            ->first();

        if (!$access) {
            return false;
        }

        // If no specific permission required, just check if user has access
        if (!$permission) {
            return true;
        }

        // Check specific permission
        $permissions = $access->pivot->permissions ? json_decode($access->pivot->permissions, true) : [];
        return in_array($permission, $permissions);
    }

    public function canCreateBusinessProfile(): bool
    {
        return $this->businessProfiles()->count() < $this->business_profile_limit;
    }

    public function getRemainingBusinessProfiles(): int
    {
        return max(0, $this->business_profile_limit - $this->businessProfiles()->count());
    }
}