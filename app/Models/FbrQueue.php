<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FbrQueue extends Model
{
    use HasFactory;

    protected $table = 'fbr_queue';

    protected $fillable = [
        'invoice_id',
        'action',
        'payload',
        'retry_count',
        'last_retry_at',
        'error_message',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'retry_count' => 'integer',
        'last_retry_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}