<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'venue_id' => (int) $this->venue_id,
            'name' => $this->name,
            'sport_type' => $this->sport_type,
            'image_url' => $this->primary_image_url,
            'images' => $this->images ?: [$this->primary_image_url],
            'is_active' => (bool) $this->is_active,

            'operating_hours' => $this->whenLoaded('operatingHours'),
            'court_prices' => CourtPriceResource::collection($this->whenLoaded('courtPrices')),
            'schedule_blocks' => ScheduleBlockResource::collection($this->whenLoaded('scheduleBlocks')),
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

