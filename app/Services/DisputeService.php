<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Provider;

class DisputeService
{
    public function __construct(private AgentTraceService $agentTrace) {}

    public function open(Booking $booking, string $triggerType, string $description): Dispute
    {
        $dispute = Dispute::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'trigger_type' => $triggerType,
            'description' => $description,
            'stage' => 1,
        ]);

        $dispute->update(['resolution_offer' => $this->stageOneOffer($dispute)]);

        $this->agentTrace->log(
            $booking->agent_run_id ?? \Illuminate\Support\Str::uuid(),
            'dispute',
            6,
            compact('triggerType', 'description'),
            $dispute->toArray(),
            'Dispute opened. Stage 1 offer generated automatically.',
            0.95,
            0
        );

        return $dispute->fresh();
    }

    public function stageOneOffer(Dispute $dispute): string
    {
        return match ($dispute->trigger_type) {
            'no_show' => 'We can offer a full refund or reschedule at no extra cost.',
            'late' => 'We can offer a 10% discount on your next booking as compensation.',
            'quality' => 'We can offer a 20% refund or a free re-service within 48 hours.',
            'price' => 'We will review the pricing and refund any overcharge within 24 hours.',
            'damage' => 'We will arrange a damage assessment and cover repair costs up to PKR 5,000.',
            'refund' => 'We will process your refund within 3-5 business days.',
            default => 'We are reviewing your complaint and will respond within 24 hours.',
        };
    }

    public function advance(Dispute $dispute): void
    {
        if ($dispute->stage < 3) {
            $newStage = $dispute->stage + 1;
            $dispute->update([
                'stage' => $newStage,
                'human_flag' => $newStage >= 3,
            ]);
        }
    }

    public function resolve(Dispute $dispute, string $outcome): void
    {
        $dispute->update([
            'outcome' => $outcome,
            'resolved_at' => now(),
        ]);

        $provider = $dispute->booking->provider;

        if (in_array($outcome, ['refunded', 'blacklisted'])) {
            $provider->increment('warning_count');
            $this->checkBlacklist($provider);
        }
    }

    public function checkBlacklist(Provider $provider): void
    {
        $noShows = Dispute::whereHas('booking', fn ($q) => $q->where('provider_id', $provider->id))
            ->where('trigger_type', 'no_show')->count();

        $damage = Dispute::whereHas('booking', fn ($q) => $q->where('provider_id', $provider->id))
            ->where('trigger_type', 'damage')
            ->where('outcome', 'resolved')->count();

        if ($noShows >= 3 || $damage >= 2) {
            $provider->update(['status' => 'blacklisted']);
        }
    }
}
