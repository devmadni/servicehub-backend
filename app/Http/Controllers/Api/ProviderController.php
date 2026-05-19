<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Models\Booking;
use App\Models\Provider;
use App\Services\AgentTraceService;
use App\Services\ProviderMatchingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProviderMatchingService $matcher,
        private AgentTraceService $agentTrace
    ) {}

    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'service_type' => 'nullable|string',
            'complexity' => 'nullable|in:basic,intermediate,complex',
            'urgency' => 'nullable|in:low,normal,high,emergency',
        ]);

        $intent = [
            'service_type' => $request->service_type ?? '',
            'complexity' => $request->complexity ?? 'basic',
            'urgency' => $request->urgency ?? 'normal',
            'user_lat' => $request->lat,
            'user_lng' => $request->lng,
        ];

        $runId = Str::uuid()->toString();
        $start = now();
        $ranked = $this->matcher->rank($intent, $request->lat, $request->lng, auth()->user());

        $this->agentTrace->log(
            $runId, 'matching', 2,
            $intent,
            ['ranked_count' => count($ranked)],
            'Providers ranked by distance. Top 5 nearest returned with slots and pricing.',
            0.95,
            now()->diffInMilliseconds($start)
        );

        return $this->success([
            'providers' => $ranked,
            'meta' => [
                'complexity' => $intent['complexity'],
                'urgency' => $intent['urgency'],
                'ranked_count' => count($ranked),
                'agent_run_id' => $runId,
            ],
        ], 'Providers ranked');
    }

    public function show(Provider $provider): JsonResponse
    {
        $provider->load(['category', 'reputations' => fn ($q) => $q->latest()->limit(5)]);

        return $this->success(new ProviderResource($provider));
    }

    public function availability(Provider $provider): JsonResponse
    {
        $bookedSlots = Booking::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'confirmed', 'en_route'])
            ->where('slot_datetime', '>=', now())
            ->where('slot_datetime', '<=', now()->addDays(7))
            ->pluck('slot_datetime')
            ->map(fn ($dt) => $dt->toDateTimeString());

        $available = [];
        $cursor = now()->startOfHour()->addHour();
        $end = now()->addDays(7);

        while ($cursor <= $end) {
            if ($cursor->isWeekday() && $cursor->hour >= 8 && $cursor->hour <= 20) {
                $slotStr = $cursor->toDateTimeString();
                if (! $bookedSlots->contains($slotStr)) {
                    $available[] = $slotStr;
                }
            }
            $cursor->addHours(2);
        }

        return $this->success([
            'slots' => array_slice($available, 0, 20),
            'provider_id' => $provider->id,
        ], 'Available slots');
    }
}
