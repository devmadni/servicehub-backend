<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Provider;
use App\Models\ProviderReputation;

class ReputationService
{
    public function recalculate(Provider $provider, Booking $booking, string $trigger, float $ratingGiven): array
    {
        $scoreBefore = (float) $provider->rating_avg;

        // Weighted rolling average incorporating all-time rating
        $totalBookings = $provider->bookings()->where('status', 'completed')->count();
        $newAvg = (($scoreBefore * max(1, $totalBookings - 1)) + $ratingGiven) / $totalBookings;
        $newAvg = round(min(5.0, max(1.0, $newAvg)), 2);

        $delta = round($newAvg - $scoreBefore, 4);

        ProviderReputation::create([
            'provider_id' => $provider->id,
            'score_before' => $scoreBefore,
            'score_after' => $newAvg,
            'trigger' => $trigger,
            'booking_id' => $booking->id,
            'delta' => $delta,
        ]);

        $provider->update(['rating_avg' => $newAvg]);

        return [
            'score_before' => $scoreBefore,
            'score_after' => $newAvg,
            'delta' => $delta,
            'new_rating_avg' => $newAvg,
        ];
    }
}
