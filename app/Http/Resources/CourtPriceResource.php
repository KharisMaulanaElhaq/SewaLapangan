<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'court_id' => $this->court_id,
            'day_type' => $this->day_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'price' => (float) $this->price,
        ];
    }
}
