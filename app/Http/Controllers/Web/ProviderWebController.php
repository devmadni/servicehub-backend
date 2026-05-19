<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderWebController extends Controller
{
    public function dashboard(): View
    {
        $provider = auth()->user()->provider()->first();
        abort_unless($provider, 403, 'No provider profile found.');

        $todayBookings = Booking::where('provider_id', $provider->id)
            ->whereDate('slot_datetime', today())
            ->with(['user', 'serviceRequest'])
            ->orderBy('slot_datetime')
            ->get();

        return view('provider.dashboard', [
            'provider' => $provider,
            'todayBookings' => $todayBookings,
            'stats' => [
                'active' => Booking::where('provider_id', $provider->id)
                    ->whereIn('status', ['confirmed', 'en_route'])->count(),
                'completed' => Booking::where('provider_id', $provider->id)
                    ->where('status', 'completed')->count(),
            ],
        ]);
    }

    public function bookings(Request $request): View
    {
        $provider = auth()->user()->provider()->first();
        abort_unless($provider, 403);

        $query = Booking::where('provider_id', $provider->id)
            ->with(['user', 'serviceRequest']);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('provider.bookings', compact('bookings'));
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $provider = auth()->user()->provider()->first();
        abort_if($booking->provider_id !== $provider?->id, 403);

        $request->validate(['status' => 'required|in:en_route,completed']);

        $updates = ['status' => $request->status];
        if ($request->status === 'completed') {
            $updates['completed_at'] = now();
        }

        $booking->update($updates);

        return back()->with('success', "Booking marked as {$request->status}.");
    }

    public function profile(): View
    {
        $provider = auth()->user()->provider()->with('category')->first();
        abort_unless($provider, 403);

        $reputations = $provider->reputations()->latest()->take(5)->get();

        return view('provider.profile', compact('provider', 'reputations'));
    }
}
