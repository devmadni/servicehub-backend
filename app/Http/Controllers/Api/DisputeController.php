<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisputeRequest;
use App\Http\Resources\DisputeResource;
use App\Models\Booking;
use App\Models\Dispute;
use App\Services\DisputeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    use ApiResponse;
    public function __construct(private DisputeService $disputeService) {}

    public function index(): JsonResponse
    {
        $disputes = Dispute::where('user_id', auth()->id())
            ->with('booking')
            ->latest()
            ->paginate(15);

        return $this->paginated($disputes);
    }

    public function store(StoreDisputeRequest $request): JsonResponse
    {
        $booking = Booking::where('user_id', auth()->id())
            ->findOrFail($request->booking_id);

        $dispute = $this->disputeService->open(
            $booking,
            $request->trigger_type,
            $request->description
        );

        return $this->created([
            'dispute_id' => $dispute->id,
            'stage' => $dispute->stage,
            'resolution_offer' => $dispute->resolution_offer,
            'options' => ['accept_refund', 'accept_reservice', 'reject_escalate'],
            'expires_at' => now()->addHours(2)->toDateTimeString(),
        ], 'Dispute opened');
    }

    public function show(Dispute $dispute): JsonResponse
    {
        abort_if($dispute->user_id !== auth()->id(), 403);

        return $this->success(new DisputeResource($dispute));
    }

    public function resolve(Request $request, Dispute $dispute): JsonResponse
    {
        abort_if($dispute->user_id !== auth()->id(), 403);

        $request->validate([
            'action' => 'required|in:accept_refund,accept_reservice,reject_escalate',
        ]);

        if ($request->action === 'reject_escalate') {
            $this->disputeService->advance($dispute);
            $fresh = $dispute->fresh();

            return $this->success([
                'dispute_id' => $dispute->id,
                'stage' => $fresh->stage,
                'human_flag' => $fresh->human_flag,
            ], 'Dispute escalated to next stage.');
        }

        $outcome = $request->action === 'accept_refund' ? 'refunded' : 'resolved';
        $this->disputeService->resolve($dispute, $outcome);

        return $this->success(new DisputeResource($dispute->fresh()), 'Dispute resolved.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->methodNotAllowed('Use /resolve endpoint.');
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->methodNotAllowed('Not allowed.');
    }
}
