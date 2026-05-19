<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ProviderMatchingService
{
    public function rank(array $intent, float $lat, float $lng): array
    {
        $providers = $this->filterByComplexity($intent['complexity'] ?? 'basic');

        $scored = $providers->map(function (Provider $p) use ($lat, $lng, $intent) {
            return [
                'provider' => $p,
                'score' => $this->score($p, $lat, $lng, $intent),
                'travel_time_min' => $this->getTravelTime($lat, $lng, $p),
            ];
        })->sortByDesc('score')->take(3)->values();

        return $scored->map(function ($item) use ($lat, $lng, $intent) {
            $p = $item['provider'];
            $scores = $this->getFactorScores($p, $lat, $lng, $intent);

            return array_merge($p->toArray(), [
                'match_score' => $item['score'],
                'travel_time_min' => $item['travel_time_min'],
                'distance_km' => round($this->haversineKm($lat, $lng, $p->lat, $p->lng), 1),
                'reasoning' => $this->buildReasoning($p, $scores),
            ]);
        })->toArray();
    }

    public function filterByComplexity(string $complexity): Collection
    {
        $minExperience = match ($complexity) {
            'complex' => 5,
            'intermediate' => 2,
            default => 0,
        };

        return Provider::where('status', 'active')
            ->where('experience_years', '>=', $minExperience)
            ->get();
    }

    public function score(Provider $p, float $lat, float $lng, array $intent): float
    {
        $scores = $this->getFactorScores($p, $lat, $lng, $intent);

        return round(
            $scores['rating'] * 0.22 +
            $scores['on_time'] * 0.18 +
            $scores['travel'] * 0.15 +
            $scores['specialization'] * 0.13 +
            $scores['cancel'] * 0.10 +
            $scores['price'] * 0.08 +
            $scores['recency'] * 0.06 +
            $scores['capacity'] * 0.04 +
            $scores['preference'] * 0.03 +
            $scores['risk'] * 0.01,
            4
        );
    }

    public function buildReasoning(Provider $p, array $scores): string
    {
        $parts = [];

        if ($p->rating_avg >= 4.5) {
            $parts[] = "top-rated ({$p->rating_avg}/5)";
        }
        if ($p->on_time_score >= 90) {
            $parts[] = "excellent on-time rate ({$p->on_time_score}%)";
        }
        if ($scores['travel'] > 0.7) {
            $parts[] = 'nearby';
        }
        if ($p->cancel_rate <= 5) {
            $parts[] = 'low cancellation rate';
        }

        return ucfirst(implode(', ', $parts) ?: 'Good match for your request');
    }

    public function getTravelTime(float $lat, float $lng, Provider $p): int
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            // Fallback: estimate from haversine distance at 30 km/h average
            $km = $this->haversineKm($lat, $lng, $p->lat, $p->lng);

            return (int) ($km / 30 * 60);
        }

        try {
            $response = Http::get(config('services.google_maps.base_url').'/distancematrix/json', [
                'origins' => "{$lat},{$lng}",
                'destinations' => "{$p->lat},{$p->lng}",
                'key' => $key,
                'mode' => 'driving',
            ]);

            $seconds = $response->json('rows.0.elements.0.duration.value');

            return $seconds ? (int) ($seconds / 60) : 30;
        } catch (\Exception) {
            $km = $this->haversineKm($lat, $lng, $p->lat, $p->lng);

            return (int) ($km / 30 * 60);
        }
    }

    private function getFactorScores(Provider $p, float $lat, float $lng, array $intent): array
    {
        $travelMin = $this->getTravelTime($lat, $lng, $p);

        return [
            'rating' => $p->rating_avg / 5,
            'on_time' => $p->on_time_score / 100,
            'travel' => max(0, (60 - $travelMin) / 60),
            'specialization' => $this->specializationMatch($p, $intent),
            'cancel' => 1 - ($p->cancel_rate / 100),
            'price' => max(0, 1 - ($p->price_min / 5000)),
            'recency' => 0.7, // Simplified: would use recent review sentiment
            'capacity' => max(0, 1 - ($p->capacity_current / 10)),
            'preference' => 0.5, // Simplified: would check user history
            'risk' => 1 - $p->risk_score,
        ];
    }

    private function specializationMatch(Provider $p, array $intent): float
    {
        $serviceType = strtolower($intent['service_type'] ?? '');
        $specializations = array_map('strtolower', $p->specializations ?? []);

        foreach ($specializations as $spec) {
            if (str_contains($serviceType, $spec) || str_contains($spec, $serviceType)) {
                return 1.0;
            }
        }

        return 0.3;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
