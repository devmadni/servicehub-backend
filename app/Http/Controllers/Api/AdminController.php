<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Models\Booking;
use App\Models\Provider;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use ApiResponse;

    public function providers(): JsonResponse
    {
        $providers = Provider::with('category')->paginate(25);

        return $this->paginated($providers);
    }

    public function updateProvider(Request $request, Provider $provider): JsonResponse
    {
        $provider->update($request->validate([
            'name' => 'sometimes|string|max:120',
            'area' => 'sometimes|string|max:80',
            'price_min' => 'sometimes|integer|min:0',
            'specializations' => 'sometimes|array',
            'experience_years' => 'sometimes|integer|min:0',
        ]));

        return $this->success(new ProviderResource($provider->fresh()), 'Provider updated');
    }

    public function updateProviderStatus(Request $request, Provider $provider): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,suspended,blacklisted']);
        $provider->update(['status' => $request->status]);

        return $this->success(new ProviderResource($provider->fresh()), 'Provider status updated');
    }

    public function bookings(): JsonResponse
    {
        $bookings = Booking::with(['user', 'provider', 'serviceRequest'])->latest()->paginate(25);

        return $this->paginated($bookings);
    }
}
