<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ProviderMatchingService
{
    public function __construct(private GooglePlacesService $places) {}

    public function rank(array $intent, float $lat, float $lng, ?User $user = null): array
    {
        $providers = $this->filterByComplexity($intent['complexity'] ?? 'basic', $intent['service_type'] ?? '', $lat, $lng);

        $scored = $providers->map(function (Provider $p) use ($lat, $lng, $intent) {
            return [
                'provider' => $p,
                'score' => $this->score($p, $lat, $lng, $intent),
                'distance_km' => $this->haversineKm($lat, $lng, $p->lat, $p->lng),
                'travel_time_min' => $this->getTravelTime($lat, $lng, $p),
            ];
        })->sortBy('distance_km')->take(5)->values();

        return $scored->map(function ($item) use ($lat, $lng, $intent, $user) {
            $p = $item['provider'];
            $scores = $this->getFactorScores($p, $lat, $lng, $intent);

            return array_merge($p->toArray(), [
                'score' => $item['score'],
                'travel_time_min' => $item['travel_time_min'],
                'distance_km' => round($item['distance_km'], 1),
                'reason' => $this->buildReasoning($p, $scores),
                'available_slots' => $this->getAvailableSlots($p),
                'pricing' => $this->estimatePricing($p, $intent, $user),
            ]);
        })->toArray();
    }

    public function filterByComplexity(string $complexity, string $serviceType = '', float $lat = 0.0, float $lng = 0.0): Collection
    {
        if ($serviceType !== '' && ($lat !== 0.0 || $lng !== 0.0)) {
            $this->places->searchAndUpsert($serviceType, $lat, $lng);
        }

        $minExperience = match ($complexity) {
            'complex' => 5,
            'intermediate' => 2,
            default => 0,
        };

        $query = Provider::with('category')
            ->where('status', 'active')
            ->where('experience_years', '>=', $minExperience);

        if ($serviceType !== '') {
            $categoryId = Category::whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($serviceType).'%'])
                ->value('id');

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        }

        return $query->get();
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

    private function getAvailableSlots(Provider $p): array
    {
        $bookedSlots = Booking::where('provider_id', $p->id)
            ->whereIn('status', ['pending', 'confirmed', 'en_route'])
            ->where('slot_datetime', '>=', now())
            ->where('slot_datetime', '<=', now()->addDays(3))
            ->pluck('slot_datetime')
            ->map(fn ($dt) => $dt->toDateTimeString());

        $slots = [];
        $cursor = now()->startOfHour()->addHour();
        $end = now()->addDays(3);

        while ($cursor <= $end && count($slots) < 5) {
            if ($cursor->isWeekday() && $cursor->hour >= 8 && $cursor->hour <= 20) {
                $slotStr = $cursor->toDateTimeString();
                if (! $bookedSlots->contains($slotStr)) {
                    $slots[] = [
                        'datetime' => $slotStr,
                        'label' => $cursor->format('D, M j g:i A'),
                    ];
                }
            }
            $cursor->addHours(2);
        }

        return $slots;
    }

    private function estimatePricing(Provider $p, array $intent, ?User $user): array
    {
        $base = $p->price_min;
        $visitFee = 150;
        $urgencyAdj = match ($intent['urgency'] ?? 'normal') {
            'emergency' => 500,
            'high' => 200,
            'low' => -50,
            default => 0,
        };
        $surge = $p->capacity_current > 8 ? 1.15 : 1.00;
        $subtotal = (int) (($base + $visitFee + $urgencyAdj) * $surge);
        $loyaltyDiscount = ($user && ($user->booking_count ?? 0) >= 3) ? (int) ($subtotal * 0.05) : 0;

        return [
            'base_rate' => $base,
            'visit_fee' => $visitFee,
            'urgency_adj' => $urgencyAdj,
            'surge_factor' => $surge,
            'loyalty_discount' => $loyaltyDiscount,
            'estimated_total' => $subtotal - $loyaltyDiscount,
            'currency' => 'PKR',
        ];
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
