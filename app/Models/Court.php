<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'sport_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class, 'court_id');
    }

    public function courtPrices(): HasMany
    {
        return $this->hasMany(CourtPrice::class, 'court_id');
    }

    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class, 'court_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'court_id');
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class, 'court_id');
    }
}
