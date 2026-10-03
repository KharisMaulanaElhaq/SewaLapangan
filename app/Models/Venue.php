<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'address',
        'city',
        'phone',
        'dp_percent',
        'cancel_free_hours',
        'cancel_refund_percent',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'dp_percent' => 'integer',
            'cancel_free_hours' => 'integer',
            'cancel_refund_percent' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class, 'venue_id');
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Court::class, 'venue_id', 'court_id');
    }
}
