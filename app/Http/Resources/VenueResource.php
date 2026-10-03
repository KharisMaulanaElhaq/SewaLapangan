<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'dp_percent' => (int) $this->dp_percent,
            'cancel_free_hours' => (int) $this->cancel_free_hours,
            'cancel_refund_percent' => (int) $this->cancel_refund_percent,
            'image_url' => $this->image_url,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
