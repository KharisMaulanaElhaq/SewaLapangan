<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'order_id' => $this->order_id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'gateway_ref' => $this->gateway_ref,
            'snap_token' => $this->snap_token,
            'snap_redirect_url' => $this->snap_redirect_url,
            'redirect_url' => $this->snap_redirect_url,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'paid_at_local' => $this->paid_at ? Carbon::parse($this->paid_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
