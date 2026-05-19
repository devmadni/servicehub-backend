<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
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
            'trigger_type' => $this->trigger_type,
            'description' => $this->description,
            'stage' => $this->stage,
            'resolution_offer' => $this->resolution_offer,
            'outcome' => $this->outcome,
            'human_flag' => $this->human_flag,
            'refund_amount' => $this->refund_amount,
            'resolved_at' => $this->resolved_at?->toDateTimeString(),
            'booking' => $this->whenLoaded('booking'),
        ];
    }
}
