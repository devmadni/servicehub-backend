<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ConflictException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Services\BookingSimulatorService;
use App\Services\DisputeService;
use App\Services\IntentParserService;
use App\Services\PricingEngine;
use App\Services\ProviderMatchingService;
use App\Services\ReputationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private IntentParserService $intentParser,
        private ProviderMatchingService $matcher,
        private PricingEngine $pricing,
        private BookingSimulatorService $simulator,
        private DisputeService $disputeService,
        private ReputationService $reputation,
    ) {}

    public function dashboard(): View
    {
        $userId = auth()->id();
        $bookings = Booking::where('user_id', $userId)
            ->with(['provider', 'serviceRequest', 'pricingQuote'])
            ->latest()->take(5)->get();

        return view('user.dashboard', [
            'bookings' => $bookings,
            'stats' => [
                'total' => Booking::where('user_id', $userId)->count(),
                'confirmed' => Booking::where('user_id', $userId)->where('status', 'confirmed')->count(),
                'completed' => Booking::where('user_id', $userId)->where('status', 'completed')->count(),
                'disputes' => Dispute::where('user_id', $userId)->whereNull('outcome')->count(),
            ],
        ]);
    }

    public function showRequest(): View
    {
        return view('user.request');
    }

    public function storeRequest(Request $request): RedirectResponse|View
    {
        $request->validate([
            'input' => 'required|string|min:5|max:500',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $runId = Str::uuid()->toString();
        $result = $this->intentParser->parse($request->input, $request->lat, $request->lng);

        if (! ($result['proceed'] ?? false)) {
            return back()->with('clarification', $result['clarification_question'])->withInput();
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => auth()->id(),
            'raw_input' => $request->input,
            'detected_lang' => $result['detected_lang'] ?? 'english',
            'service_type' => $result['service_type'] ?? 'General Service',
            'location' => $result['location'] ?? null,
            'user_lat' => $request->lat,
            'user_lng' => $request->lng,
            'urgency' => $result['urgency'] ?? 'normal',
            'budget_sensitivity' => $result['budget_sensitivity'] ?? 'normal',
            'requested_datetime' => $result['preferred_time'] ?? null,
            'issue_severity' => $result['issue_severity'] ?? 'low',
            'confidence' => $result['confidence'] ?? 0,
            'complexity' => $result['complexity'] ?? 'basic',
            'agent_run_id' => $runId,
        ]);

        $ranked = $this->matcher->rank(
            $serviceRequest->toArray(),
            $request->lat,
            $request->lng
        );

        return view('user.providers', [
            'providers' => $ranked,
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function getQuote(Request $request): View
    {
        $request->validate([
            'provider_id' => 'required|integer|exists:providers,id',
            'service_request_id' => 'required|integer|exists:service_requests,id',
        ]);

        $provider = Provider::findOrFail($request->provider_id);
        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);
        $user = auth()->user();

        $intent = [
            'user_lat' => $serviceRequest->user_lat ?? 0,
            'user_lng' => $serviceRequest->user_lng ?? 0,
            'urgency' => $serviceRequest->urgency,
            'budget_sensitivity' => $serviceRequest->budget_sensitivity,
        ];

        $quoteData = $this->pricing->quote($provider, $intent, $user);
        $quote = $this->pricing->saveQuote($quoteData, $provider->id);

        $budgetQuote = null;
        if ($serviceRequest->budget_sensitivity === 'high') {
            $budgetData = $this->pricing->budgetQuote($intent, $user);
            if ($budgetData) {
                $budgetQuote = $this->pricing->saveQuote($budgetData, $provider->id, true, $quote->id);
            }
        }

        return view('user.quote', compact('provider', 'serviceRequest', 'quote', 'budgetQuote'));
    }

    public function bookings(): View
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['provider', 'serviceRequest', 'pricingQuote'])
            ->latest()->paginate(10);

        return view('user.bookings.index', compact('bookings'));
    }

    public function showBooking(Booking $booking): View
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $booking->load(['provider', 'serviceRequest', 'pricingQuote', 'disputes']);

        return view('user.bookings.show', compact('booking'));
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $request->validate([
            'provider_id' => 'required|integer|exists:providers,id',
            'service_request_id' => 'required|integer|exists:service_requests,id',
            'pricing_quote_id' => 'nullable|integer|exists:pricing_quotes,id',
            'slot_datetime' => 'required|date|after:now',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);

        try {
            $booking = $this->simulator->create([
                'user_id' => auth()->id(),
                'provider_id' => $request->provider_id,
                'service_request_id' => $request->service_request_id,
                'pricing_quote_id' => $request->pricing_quote_id,
                'slot_datetime' => $request->slot_datetime,
                'complexity' => $serviceRequest->complexity,
                'agent_run_id' => $serviceRequest->agent_run_id,
            ]);
        } catch (ConflictException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('user.bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_ref} created successfully!");
    }

    public function confirmBooking(Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $this->simulator->confirm($booking);

        return back()->with('success', 'Booking confirmed!');
    }

    public function cancelBooking(Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        $booking->update(['status' => 'cancelled']);

        return redirect()->route('user.bookings')->with('success', 'Booking cancelled.');
    }

    public function submitFeedback(Request $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        $this->reputation->recalculate($booking->provider, $booking, 'feedback', $request->rating);
        auth()->user()->increment('booking_count');

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function disputes(): View
    {
        $disputes = Dispute::where('user_id', auth()->id())->with('booking')->latest()->paginate(10);

        return view('user.disputes.index', compact('disputes'));
    }

    public function createDispute(): View
    {
        return view('user.disputes.create');
    }

    public function storeDispute(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'trigger_type' => 'required|in:no_show,late,quality,price,damage,refund',
            'description' => 'required|string|min:10|max:2000',
        ]);

        $booking = Booking::where('user_id', auth()->id())->findOrFail($request->booking_id);
        $dispute = $this->disputeService->open($booking, $request->trigger_type, $request->description);

        return redirect()->route('user.disputes')->with('success', "Dispute opened. Resolution offer: {$dispute->resolution_offer}");
    }
}
