<?php

namespace App\Services;

use App\Models\PricingQuote;
use App\Models\Provider;
use App\Models\User;

class PricingEngine
{
    public function quote(Provider $p, array $intent, User $user): array
    {
        $base = $p->price_min;
        $visit = 150;
        $distance = $this->distanceCost($intent['user_lat'] ?? 0, $intent['user_lng'] ?? 0, $p);
        $urgency = $this->urgencyAdjustment($intent['urgency'] ?? 'normal');
        $surge = $this->surgeFactor($p);
        $subtotal = (int) (($base + $visit + $distance + $urgency) * $surge);
        $loyalty = $this->loyaltyDiscount($user, $subtotal);
        $total = $subtotal - $loyalty;

        return [
            'base_rate' => $base,
            'visit_fee' => $visit,
            'distance_cost' => $distance,
            'urgency_adj' => $urgency,
            'surge_factor' => $surge,
            'loyalty_discount' => $loyalty,
            'total' => $total,
            'provider_net' => $this->providerNet($total),
        ];
    }

    public function budgetQuote(array $intent, User $user): ?array
    {
        $budgetProvider = Provider::where('status', 'active')
            ->where('price_min', '<', 800)
            ->orderBy('price_min')
            ->first();

        if (! $budgetProvider) {
            return null;
        }

        return $this->quote($budgetProvider, $intent, $user);
    }

    public function surgeFactor(Provider $p): float
    {
        return $p->capacity_current > 8 ? 1.15 : 1.00;
    }

    public function loyaltyDiscount(User $user, int $base): int
    {
        if ($user->booking_count >= 3) {
            return (int) ($base * 0.05);
        }

        return 0;
    }

    public function providerNet(int $total): int
    {
        return (int) ($total * 0.85);
    }

    public function saveQuote(array $quoteData, int $providerId, bool $isBudget = false, ?int $altQuoteId = null): PricingQuote
    {
        return PricingQuote::create(array_merge($quoteData, [
            'provider_id' => $providerId,
            'is_budget_tier' => $isBudget,
            'alt_quote_id' => $altQuoteId,
        ]));
    }

    private function distanceCost(float $lat, float $lng, Provider $p): int
    {
        $dLat = deg2rad($p->lat - $lat);
        $dLng = deg2rad($p->lng - $lng);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($p->lat)) * sin($dLng / 2) ** 2;
        $km = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) ($km * 30); // PKR 30 per km
    }

    private function urgencyAdjustment(string $urgency): int
    {
        return match ($urgency) {
            'emergency' => 500,
            'high' => 200,
            'low' => -50,
            default => 0,
        };
    }
}
