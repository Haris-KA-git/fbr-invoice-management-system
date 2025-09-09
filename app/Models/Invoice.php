<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'customer_id',
        'user_id',
        'invoice_number',
        'fbr_invoice_number',
        'invoice_date',
        'invoice_type',
        'status',
        'subtotal',
        'discount_amount',
        'sales_tax',
        'fed_amount',
        'further_tax',
        'withheld_tax',
        'total_amount',
        'fbr_json_data',
        'fbr_status',
        'fbr_response',
        'fbr_error_message',
        'discard_reason',
        'discarded_at',
        'discarded_by',
        'qr_code',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'discarded_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sales_tax' => 'decimal:2',
        'fed_amount' => 'decimal:2',
        'further_tax' => 'decimal:2',
        'withheld_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'fbr_json_data' => 'array',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function fbrQueue(): HasMany
    {
        return $this->hasMany(FbrQueue::class);
    }

    public function discardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discarded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeDiscarded($query)
    {
        return $query->where('status', 'discarded');
    }
}