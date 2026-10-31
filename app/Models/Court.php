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
        'image_url',
        'images',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'images' => 'array',
        ];
    }

    /**
     * Get primary display image URL with sport-specific fallback.
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        if (!empty($this->image_url)) {
            return str_starts_with($this->image_url, 'http')
                ? $this->image_url
                : asset($this->image_url);
        }

        if (!empty($this->images) && is_array($this->images) && count($this->images) > 0) {
            $first = $this->images[0];
            return str_starts_with($first, 'http') ? $first : asset($first);
        }

        return match (strtolower($this->sport_type)) {
            'futsal' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80',
            'badminton' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=800&q=80',
            'basketball' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80',
            'mini_soccer' => 'https://images.unsplash.com/photo-1529900245534-47fbfb57835a?auto=format&fit=crop&w=800&q=80',
            'tennis' => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=800&q=80',
            default => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80',
        };
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
