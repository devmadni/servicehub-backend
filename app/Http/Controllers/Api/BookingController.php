<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ServiceRequest;
use App\Services\BookingSimulatorService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponse;
    public function __construct(private BookingSimulatorService $simulator) {}

    public function index(): JsonResponse
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['provider', 'serviceRequest', 'pricingQuote'])
            ->latest()
            ->paginate(15);

        return $this->paginated($bookings);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);

        try {
            $booking = $this->simulator->create(array_merge($request->validated(), [
                'user_id' => auth()->id(),
                'complexity' => $serviceRequest->complexity,
                'agent_run_id' => $serviceRequest->agent_run_id,
            ]));
        } catch (ConflictException $e) {
            return $this->conflict($e->getMessage());
        }

        return $this->created([
            'id' => $booking->id,
            'booking_ref' => $booking->booking_ref,
            'status' => $booking->status,
            'conflict_check' => 'PASSED',
            'before_state' => ['slot_'.$booking->slot_datetime->format('Hi') => 'OPEN'],
            'after_state' => [
                'slot_'.$booking->slot_datetime->format('Hi') => 'RESERVED',
                'booking_ref' => $booking->booking_ref,
            ],
        ], 'Booking created');
    }

    public function show(Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $booking->load(['provider', 'serviceRequest', 'pricingQuote', 'disputes']);

        return $this->success(new BookingResource($booking));
    }

    public function confirm(Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_if($booking->status !== 'pending', 422, 'Booking is not in pending state.');

        $result = $this->simulator->confirm($booking);

        return $this->success($result, 'Booking confirmed');
    }

    public function receipt(Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        return $this->success($this->simulator->buildReceipt($booking), 'Receipt retrieved');
    }

    public function destroy(Booking $booking): JsonResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_if(in_array($booking->status, ['completed', 'disputed']), 422, 'Cannot cancel this booking.');

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => request()->input('reason'),
        ]);

        return $this->noContent('Booking cancelled.');
    }
}
