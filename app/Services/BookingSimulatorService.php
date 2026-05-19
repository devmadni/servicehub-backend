<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingSimulatorService
{
    public function __construct(private AgentTraceService $agentTrace) {}

    public function create(array $data): Booking
    {
        $slot = Carbon::parse($data['slot_datetime']);

        if (! $this->conflictCheck($data['provider_id'], $slot)) {
            throw new ConflictException('Slot unavailable — please choose another time.');
        }

        $booking = Booking::create([
            'booking_ref' => $this->generateRef(),
            'user_id' => $data['user_id'],
            'provider_id' => $data['provider_id'],
            'service_request_id' => $data['service_request_id'],
            'pricing_quote_id' => $data['pricing_quote_id'] ?? null,
            'slot_datetime' => $data['slot_datetime'],
            'slot_end_datetime' => $slot->copy()->addHours(2),
            'complexity' => $data['complexity'] ?? 'basic',
            'status' => 'pending',
            'simulated' => true,
            'agent_run_id' => $data['agent_run_id'] ?? null,
        ]);

        $this->agentTrace->log(
            $data['agent_run_id'] ?? Str::uuid(),
            'booking',
            4,
            $data,
            $booking->toArray(),
            'Conflict check passed. Booking record created.',
            0.99,
            0
        );

        return $booking;
    }

    public function confirm(Booking $booking): array
    {
        $before = ['status' => $booking->status, 'slot' => $booking->slot_datetime];

        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $after = ['status' => $booking->fresh()->status, 'confirmed_at' => $booking->confirmed_at];

        return [
            'booking' => $booking->fresh()->load(['provider', 'user', 'pricingQuote']),
            'before_state' => $before,
            'after_state' => $after,
            'sms_payload' => $this->smsPayload($booking),
            'receipt' => $this->buildReceipt($booking->fresh()),
        ];
    }

    public function conflictCheck(int $providerId, Carbon $slot): bool
    {
        $buffer = 30; // minutes

        $conflicting = Booking::where('provider_id', $providerId)
            ->whereIn('status', ['pending', 'confirmed', 'en_route'])
            ->where(function ($q) use ($slot, $buffer) {
                $q->whereBetween('slot_datetime', [
                    $slot->copy()->subMinutes($buffer),
                    $slot->copy()->addHours(2)->addMinutes($buffer),
                ]);
            })->exists();

        return ! $conflicting;
    }

    public function generateRef(): string
    {
        return 'BK-'.strtoupper(Str::random(5));
    }

    public function buildReceipt(Booking $booking): array
    {
        $booking->loadMissing(['provider', 'pricingQuote', 'serviceRequest', 'user']);

        return [
            'booking_ref' => $booking->booking_ref,
            'service' => $booking->serviceRequest?->service_type,
            'provider' => $booking->provider?->name,
            'slot' => $booking->slot_datetime?->toDateTimeString(),
            'status' => $booking->status,
            'pricing' => $booking->pricingQuote ? [
                'base_rate' => $booking->pricingQuote->base_rate,
                'visit_fee' => $booking->pricingQuote->visit_fee,
                'distance_cost' => $booking->pricingQuote->distance_cost,
                'surge_factor' => $booking->pricingQuote->surge_factor,
                'loyalty_discount' => $booking->pricingQuote->loyalty_discount,
                'total' => $booking->pricingQuote->total,
                'provider_net' => $booking->pricingQuote->provider_net,
            ] : null,
            'customer' => $booking->user?->name,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    public function smsPayload(Booking $booking): array
    {
        $booking->loadMissing(['provider', 'serviceRequest']);

        return [
            'english' => "Booking confirmed! Ref: {$booking->booking_ref}. Provider: {$booking->provider?->name} will arrive at {$booking->slot_datetime?->format('h:i A')}.",
            'urdu' => "بکنگ تصدیق ہو گئی! حوالہ: {$booking->booking_ref}۔ {$booking->provider?->name} {$booking->slot_datetime?->format('h:i A')} پر آئیں گے۔",
        ];
    }
}
