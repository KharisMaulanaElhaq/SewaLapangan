<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_NO_SHOW = 'NO_SHOW';

    protected $fillable = [
        'code',
        'user_id',
        'court_id',
        'total_price',
        'dp_amount',
        'status',
        'expires_at',
        'cancel_free_hours_snapshot',
        'cancel_refund_percent_snapshot',
        'cancelled_at',
        'refund_amount',
        'cancellation_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancel_free_hours_snapshot' => 'integer',
            'cancel_refund_percent_snapshot' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class, 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class, 'booking_id')->latestOfMany();
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }
}
