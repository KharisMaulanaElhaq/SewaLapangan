<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'total_price' => (float) $this->total_price,
            'dp_amount' => (float) $this->dp_amount,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'expires_at_local' => Carbon::parse($this->expires_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'is_expired' => $this->expires_at ? Carbon::now()->gte($this->expires_at) : false,
            'cancel_free_hours' => (int) $this->cancel_free_hours_snapshot,
            'cancel_refund_percent' => (int) $this->cancel_refund_percent_snapshot,
            'refund_amount' => (float) $this->refund_amount,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'court' => new CourtResource($this->whenLoaded('court')),
            'slots' => BookingSlotResource::collection($this->whenLoaded('bookingSlots')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
