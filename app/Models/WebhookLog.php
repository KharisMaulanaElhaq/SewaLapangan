<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    public const STATUS_RECEIVED = 'received';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';
    public const STATUS_DUPLICATE_IGNORED = 'duplicate_ignored';

    protected $fillable = [
        'provider',
        'order_id',
        'payload',
        'signature_valid',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include failed webhook logs.
     * signature_valid = false OR status IN (error, rejected)
     */
    public function scopeFailed($query)
    {
        return $query->where(function ($q) {
            $q->where('signature_valid', false)
              ->orWhereIn('status', [self::STATUS_ERROR, self::STATUS_REJECTED]);
        });
    }
}
