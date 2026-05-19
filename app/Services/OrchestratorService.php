<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;

class OrchestratorService
{
    public function __construct(
        private IntentParserService $intentParser,
        private ProviderMatchingService $matcher,
        private PricingEngine $pricing,
        private AgentTraceService $agentTrace
    ) {}

    public function orchestrate(string $input, float $lat, float $lng, User $user): array
    {
        $runId = Str::uuid()->toString();

        // Step 1 — Intent parsing via Gemini Function Calling
        $intentStart = now();
        $intent = $this->intentParser->parse($input, $lat, $lng);
        $intentDuration = (int) abs($intentStart->diffInMilliseconds(now()));

        $this->agentTrace->log(
            $runId,
            'intent',
            1,
            ['input' => $input, 'lat' => $lat, 'lng' => $lng],
            $intent,
            'Intent parsed via Gemini function calling. Language: '.($intent['detected_lang'] ?? 'unknown').'. Complexity: '.($intent['complexity'] ?? 'basic').'.',
            $intent['confidence'] ?? 0,
            $intentDuration
        );

        if (! ($intent['proceed'] ?? false)) {
            return [
                'run_id' => $runId,
                'proceed' => false,
                'clarification_question' => $intent['clarification_question'],
                'intent' => null,
                'providers' => [],
            ];
        }

        $intentForMatcher = array_merge($intent, [
            'user_lat' => $lat,
            'user_lng' => $lng,
        ]);

        // Step 2 — Provider matching (top 5 nearest, category-filtered, with slots)
        $matchStart = now();
        $providers = $this->matcher->rank($intentForMatcher, $lat, $lng, $user);
        $matchDuration = (int) abs($matchStart->diffInMilliseconds(now()));

        $this->agentTrace->log(
            $runId,
            'matching',
            2,
            $intentForMatcher,
            ['ranked_count' => count($providers), 'provider_ids' => array_column($providers, 'id')],
            'Providers ranked by 10-factor scoring and sorted by distance. Top '.count($providers).' returned with available slots.',
            0.95,
            $matchDuration
        );

        // Step 3 — Formal pricing quotes for each matched provider (no N+1)
        $pricingStart = now();
        $providerModels = Provider::whereIn('id', array_column($providers, 'id'))
            ->get()
            ->keyBy('id');

        $quoteSummaries = [];
        $providers = array_map(function (array $p) use ($providerModels, $intentForMatcher, $user, &$quoteSummaries) {
            $model = $providerModels->get($p['id']);
            if (! $model) {
                return $p;
            }

            $quoteData = $this->pricing->quote($model, $intentForMatcher, $user);
            $quote = $this->pricing->saveQuote($quoteData, $model->id);

            $quoteSummaries[] = [
                'provider_id' => $model->id,
                'total' => $quoteData['total'],
                'surge_factor' => $quoteData['surge_factor'],
            ];

            return array_merge($p, ['pricing_quote_id' => $quote->id]);
        }, $providers);

        $pricingDuration = (int) abs($pricingStart->diffInMilliseconds(now()));

        $this->agentTrace->log(
            $runId,
            'pricing',
            3,
            ['provider_count' => count($providers)],
            ['quotes' => $quoteSummaries],
            'Formal pricing quotes saved for '.count($providers).' matched providers.',
            0.98,
            $pricingDuration
        );

        return [
            'run_id' => $runId,
            'proceed' => true,
            'intent' => [
                'service_type' => $intent['service_type'] ?? null,
                'location' => $intent['location'] ?? null,
                'urgency' => $intent['urgency'] ?? 'normal',
                'preferred_time' => $intent['preferred_time'] ?? null,
                'budget_sensitivity' => $intent['budget_sensitivity'] ?? 'normal',
                'issue_severity' => $intent['issue_severity'] ?? 'low',
                'complexity' => $intent['complexity'] ?? 'basic',
                'detected_lang' => $intent['detected_lang'] ?? 'english',
                'confidence' => $intent['confidence'] ?? 0,
            ],
            'providers' => $providers,
            'clarification_question' => null,
        ];
    }
}
