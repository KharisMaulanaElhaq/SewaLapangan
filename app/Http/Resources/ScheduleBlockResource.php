<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'court_id' => $this->court_id,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'start_at_local' => Carbon::parse($this->start_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'end_at_local' => Carbon::parse($this->end_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'reason' => $this->reason,
        ];
    }
}
