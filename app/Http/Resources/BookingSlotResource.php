<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot_start' => $this->slot_start?->toIso8601String(),
            'slot_end' => $this->slot_end?->toIso8601String(),
            'slot_start_local' => Carbon::parse($this->slot_start)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'),
            'slot_end_local' => Carbon::parse($this->slot_end)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'),
            'price' => (float) $this->price,
        ];
    }
}
