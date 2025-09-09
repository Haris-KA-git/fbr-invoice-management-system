<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'strn_ntn',
        'cnic',
        'address',
        'province_code',
        'branch_name',
        'branch_code',
        'contact_phone',
        'contact_email',
        'fbr_api_token',
        'whitelisted_ips',
        'logo_path',
        'is_sandbox',
        'is_active',
    ];

    protected $casts = [
        'whitelisted_ips' => 'array',
        'is_sandbox' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'permissions', 'is_active'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }
}