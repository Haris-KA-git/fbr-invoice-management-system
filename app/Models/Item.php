<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'item_code',
        'name',
        'description',
        'hs_code',
        'uom_id',
        'unit_of_measure',
        'tax_rate',
        'price',
        'sro_references',
        'is_active',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'price' => 'decimal:2',
        'sro_references' => 'array',
        'is_active' => 'boolean',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getUomCodeAttribute(): string
    {
        return $this->uom ? $this->uom->code : strtoupper($this->unit_of_measure);
    }

    public function getUomNameAttribute(): string
    {
        return $this->uom ? $this->uom->name : $this->unit_of_measure;
    }
}