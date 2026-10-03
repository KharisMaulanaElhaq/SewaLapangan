<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'day_type',
        'start_time',
        'end_time',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }
}
