<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\PricingQuote;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Services\AgentTraceService;
use App\Services\PricingEngine;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PricingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PricingEngine $pricing,
        private AgentTraceService $agentTrace
    ) {}

    public function quote(Request $request): JsonResponse
    {
        $request->validate([
            'provider_id' => 'required|integer|exists:providers,id',
            'service_request_id' => 'required|integer|exists:service_requests,id',
        ]);

        $provider = Provider::findOrFail($request->provider_id);
        $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);
        $user = $request->user();

        $intent = [
            'user_lat' => $serviceRequest->user_lat ?? 0,
            'user_lng' => $serviceRequest->user_lng ?? 0,
            'urgency' => $serviceRequest->urgency,
            'budget_sensitivity' => $serviceRequest->budget_sensitivity,
        ];

        $runId = $serviceRequest->agent_run_id ?? Str::uuid()->toString();
        $start = now();

        $quoteData = $this->pricing->quote($provider, $intent, $user);
        $quote = $this->pricing->saveQuote($quoteData, $provider->id);

        $budgetQuote = null;
        if ($serviceRequest->budget_sensitivity === 'high') {
            $budgetData = $this->pricing->budgetQuote($intent, $user);
            if ($budgetData) {
                $budgetQuote = $this->pricing->saveQuote($budgetData, $provider->id, true, $quote->id);
                $quote->update(['alt_quote_id' => $budgetQuote->id]);
            }
        }

        $this->agentTrace->log(
            $runId, 'pricing', 3,
            ['provider_id' => $provider->id, 'service_request_id' => $serviceRequest->id],
            ['total' => $quoteData['total'], 'surge' => $quoteData['surge_factor']],
            "Quote generated. Surge: {$quoteData['surge_factor']}x. Loyalty discount: PKR {$quoteData['loyalty_discount']}.",
            0.98,
            (int) abs($start->diffInMilliseconds(now()))
        );

        return $this->success([
            'standard' => new QuoteResource($quote),
            'budget_tier' => $budgetQuote ? new QuoteResource($budgetQuote) : null,
        ], 'Quote generated');
    }

    public function show(PricingQuote $pricingQuote): JsonResponse
    {
        return $this->success(new QuoteResource($pricingQuote));
    }
}
