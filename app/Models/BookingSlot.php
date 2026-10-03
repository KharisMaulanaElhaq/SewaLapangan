<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'court_id',
        'slot_start',
        'slot_end',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'slot_start' => 'datetime',
            'slot_end' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }
}
