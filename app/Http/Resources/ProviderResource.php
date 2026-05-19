<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'name' => $this->name,
            'area' => $this->area,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'rating_avg' => $this->rating_avg,
            'on_time_score' => $this->on_time_score,
            'cancel_rate' => $this->cancel_rate,
            'experience_years' => $this->experience_years,
            'specializations' => $this->specializations,
            'capacity_current' => $this->capacity_current,
            'risk_score' => $this->risk_score,
            'price_min' => $this->price_min,
            'status' => $this->status,
            'warning_count' => $this->warning_count,
            'category' => $this->whenLoaded('category'),
        ];
    }
}
