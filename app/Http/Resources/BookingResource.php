<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_ref' => $this->booking_ref,
            'status' => $this->status,
            'complexity' => $this->complexity,
            'slot_datetime' => $this->slot_datetime?->toDateTimeString(),
            'slot_end_datetime' => $this->slot_end_datetime?->toDateTimeString(),
            'confirmed_at' => $this->confirmed_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'simulated' => $this->simulated,
            'provider' => $this->whenLoaded('provider'),
            'service_request' => $this->whenLoaded('serviceRequest'),
            'pricing_quote' => $this->whenLoaded('pricingQuote'),
            'disputes' => $this->whenLoaded('disputes'),
        ];
    }
}
