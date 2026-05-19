<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWebController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'providers' => Provider::count(),
                'bookings' => Booking::count(),
                'disputes' => Dispute::whereNull('outcome')->count(),
                'users' => User::where('role', 'user')->count(),
            ],
            'recentBookings' => Booking::with(['user', 'serviceRequest'])
                ->latest()->take(6)->get(),
            'topProviders' => Provider::orderByDesc('rating_avg')->take(5)->get(),
        ]);
    }

    public function providers(Request $request): View
    {
        $query = Provider::with('category');
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return view('admin.providers', ['providers' => $query->paginate(20)]);
    }

    public function updateProviderStatus(Request $request, Provider $provider): RedirectResponse
    {
        $request->validate(['status' => 'required|in:active,suspended,blacklisted']);
        $provider->update(['status' => $request->status]);

        return back()->with('success', "Provider {$provider->name} set to {$request->status}.");
    }

    public function bookings(Request $request): View
    {
        $query = Booking::with(['user', 'provider', 'serviceRequest', 'pricingQuote']);
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return view('admin.bookings', ['bookings' => $query->latest()->paginate(20)]);
    }

    public function disputes(): View
    {
        $disputes = Dispute::with(['user', 'booking'])->latest()->paginate(20);

        return view('admin.disputes', compact('disputes'));
    }
}
