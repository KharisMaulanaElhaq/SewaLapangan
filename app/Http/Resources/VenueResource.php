<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $startingPrice = $this->resource->getAttribute('starting_price');

        if ($startingPrice === null && $this->relationLoaded('courts')) {
            $startingPrice = $this->courts
                ->where('is_active', true)
                ->flatMap(fn ($court) => $court->relationLoaded('courtPrices') ? $court->courtPrices : collect())
                ->min('price');
        }

        return [
            'id' => $this->id,
            'owner_id' => (int) $this->owner_id,
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
            'starting_price' => $startingPrice !== null ? (float) $startingPrice : null,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

