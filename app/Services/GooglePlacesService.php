<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GooglePlacesService
{
    private const PRICE_DEFAULTS = [
        'AC Technician' => 1200,
        'Plumber' => 800,
        'Electrician' => 900,
        'Cleaning Service' => 1500,
        'Tutor' => 600,
        'Carpenter' => 1000,
        'Painter' => 800,
        'Driver' => 700,
        'Mechanic' => 1100,
        'Beautician' => 900,
    ];

    private const SEARCH_KEYWORDS = [
        'AC Technician' => 'AC repair technician air conditioning',
        'Plumber' => 'plumber plumbing services',
        'Electrician' => 'electrician electrical services',
        'Cleaning Service' => 'home cleaning service',
        'Tutor' => 'home tutor tutoring academy',
        'Carpenter' => 'carpenter furniture repair',
        'Painter' => 'painter painting services',
        'Driver' => 'driver car service',
        'Mechanic' => 'mechanic auto repair',
        'Beautician' => 'beautician beauty salon',
    ];

    public function searchAndUpsert(string $serviceType, float $lat, float $lng): Collection
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            return collect();
        }

        $keyword = self::SEARCH_KEYWORDS[$serviceType] ?? $serviceType;

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'location' => "{$lat},{$lng}",
                'radius' => 15000,
                'keyword' => $keyword.' Karachi',
                'key' => $key,
            ]);

            if (! $response->successful()) {
                Log::warning('Google Places API error', ['status' => $response->status(), 'body' => $response->body()]);

                return collect();
            }

            $places = collect($response->json('results', []))
                ->filter(fn ($p) => ($p['business_status'] ?? '') === 'OPERATIONAL')
                ->filter(fn ($p) => isset($p['geometry']['location']['lat']))
                ->take(10);

            if ($places->isEmpty()) {
                return collect();
            }

            $category = Category::where('name', $serviceType)->first();
            $placesUser = $this->getPlacesUser();

            return $places->map(fn ($place) => $this->upsertProvider($place, $serviceType, $category?->id ?? 1, $placesUser->id));

        } catch (\Exception $e) {
            Log::warning('Google Places search failed', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    private function upsertProvider(array $place, string $serviceType, int $categoryId, int $userId): Provider
    {
        $placeId = $place['place_id'];
        $rating = (float) ($place['rating'] ?? 4.0);
        $totalRatings = (int) ($place['user_ratings_total'] ?? 10);

        return Provider::updateOrCreate(
            ['place_id' => $placeId],
            [
                'user_id' => $userId,
                'category_id' => $categoryId,
                'name' => $place['name'],
                'area' => $this->extractArea($place['vicinity'] ?? ''),
                'lat' => $place['geometry']['location']['lat'],
                'lng' => $place['geometry']['location']['lng'],
                'rating_avg' => min(5.0, max(1.0, round($rating, 2))),
                'on_time_score' => $this->estimateOnTimeScore($rating),
                'cancel_rate' => $this->estimateCancelRate($rating),
                'experience_years' => $this->estimateExperience($totalRatings),
                'specializations' => $this->extractSpecializations($place, $serviceType),
                'capacity_current' => rand(0, 4),
                'risk_score' => $this->estimateRiskScore($rating),
                'price_min' => self::PRICE_DEFAULTS[$serviceType] ?? 800,
                'status' => 'active',
                'warning_count' => 0,
                'google_maps_url' => 'https://www.google.com/maps/place/?q=place_id:'.$placeId,
            ]
        );
    }

    private function getPlacesUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'google.places.bot@ai-service.app'],
            [
                'name' => 'Google Places Bot',
                'password' => bcrypt(Str::random(32)),
                'phone' => '0000000000',
                'role' => 'provider',
            ]
        );
    }

    private function extractArea(string $vicinity): string
    {
        // vicinity is like "Shop 5, Block 3, Gulshan-e-Iqbal, Karachi"
        // grab the second-to-last segment as the area
        $parts = array_map('trim', explode(',', $vicinity));
        $count = count($parts);

        if ($count >= 2) {
            return $parts[$count - 2]; // e.g. "Gulshan-e-Iqbal"
        }

        return $parts[0] ?? 'Karachi';
    }

    private function estimateOnTimeScore(float $rating): float
    {
        // Higher rating → better on-time score
        return round(min(98, max(55, 40 + ($rating * 12))), 1);
    }

    private function estimateCancelRate(float $rating): float
    {
        // Higher rating → lower cancel rate
        return round(max(1, min(25, 30 - ($rating * 5))), 1);
    }

    private function estimateExperience(int $totalRatings): int
    {
        if ($totalRatings >= 200) {
            return 8;
        }
        if ($totalRatings >= 100) {
            return 5;
        }
        if ($totalRatings >= 50) {
            return 3;
        }
        if ($totalRatings >= 20) {
            return 2;
        }

        return 1;
    }

    private function estimateRiskScore(float $rating): float
    {
        return round(max(0, min(1, (5 - $rating) / 5 * 0.4)), 2);
    }

    private function extractSpecializations(array $place, string $serviceType): array
    {
        $specs = [$serviceType];
        $name = strtolower($place['name'] ?? '');

        $keywords = [
            'inverter' => 'Inverter', 'split' => 'Split AC', 'window' => 'Window AC',
            'commercial' => 'Commercial', 'industrial' => 'Industrial', 'solar' => 'Solar',
            'drain' => 'Drain', 'pipe' => 'Pipe Repair', 'leak' => 'Leak Detection',
            'wiring' => 'Wiring', 'cctv' => 'CCTV', 'ups' => 'UPS Installation',
            'deep' => 'Deep Clean', 'office' => 'Office Cleaning',
        ];

        foreach ($keywords as $keyword => $label) {
            if (str_contains($name, $keyword)) {
                $specs[] = $label;
            }
        }

        return array_unique($specs);
    }
}
