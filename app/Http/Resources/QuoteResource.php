<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
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
            'base_rate' => $this->base_rate,
            'visit_fee' => $this->visit_fee,
            'distance_cost' => $this->distance_cost,
            'urgency_adj' => $this->urgency_adj,
            'surge_factor' => $this->surge_factor,
            'loyalty_discount' => $this->loyalty_discount,
            'total' => $this->total,
            'provider_net' => $this->provider_net,
            'is_budget_tier' => $this->is_budget_tier,
        ];
    }
}
