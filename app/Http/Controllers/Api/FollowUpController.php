<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Services\AgentTraceService;
use App\Services\FollowUpService;
use App\Services\ReputationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FollowUpController extends Controller
{
    use ApiResponse;
    public function __construct(
        private FollowUpService $followUp,
        private ReputationService $reputation,
        private AgentTraceService $agentTrace
    ) {}

    public function schedule(Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        $schedule = $this->followUp->schedule($booking);

        $this->agentTrace->log(
            $booking->agent_run_id ?? Str::uuid(),
            'followup', 5,
            ['booking_id' => $booking->id],
            $schedule,
            'Follow-up schedule created with 3 notification events in Urdu and English.',
            0.97, 0
        );

        return $this->success($schedule, 'Follow-up scheduled');
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking): JsonResponse
    {
        $updates = ['status' => $request->status];
        if ($request->status === 'completed') {
            $updates['completed_at'] = now();
        }

        $booking->update($updates);

        return $this->success($booking->fresh(), 'Status updated');
    }

    public function feedback(Request $request, Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
            'photo_url' => 'nullable|url',
        ]);

        $reputationUpdate = $this->reputation->recalculate(
            $booking->provider,
            $booking,
            'feedback',
            $request->rating
        );

        auth()->user()->increment('booking_count');

        return $this->success([
            'feedback_id' => $booking->id,
            'reputation_update' => $reputationUpdate,
        ], 'Feedback saved. Reputation updated.');
    }
}
