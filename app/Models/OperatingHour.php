<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'day_of_week',
        'open_time',
        'close_time',
        'slot_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration_minutes' => 'integer',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }
}
