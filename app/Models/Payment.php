<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SETTLEMENT = 'settlement';
    public const STATUS_EXPIRE = 'expire';
    public const STATUS_CANCEL = 'cancel';
    public const STATUS_REFUND = 'refund';

    public const TYPE_DP = 'DP';
    public const TYPE_LUNAS = 'lunas';

    protected $fillable = [
        'booking_id',
        'order_id',
        'type',
        'amount',
        'status',
        'payment_type',
        'gateway_ref',
        'snap_token',
        'snap_redirect_url',
        'paid_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SETTLEMENT;
    }
}
