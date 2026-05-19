<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Services\IntentParserService;
use App\Services\AgentTraceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    use ApiResponse;
    public function __construct(
        private IntentParserService $intentParser,
        private AgentTraceService $agentTrace
    ) {}

    public function store(StoreServiceRequestRequest $request): JsonResponse
    {
        $runId = Str::uuid()->toString();
        $start = now();

        $result = $this->intentParser->parse(
            $request->input,
            $request->lat,
            $request->lng
        );

        $durationMs = now()->diffInMilliseconds($start);

        if (! ($result['proceed'] ?? false)) {
            return $this->success([
                'proceed' => false,
                'clarification_question' => $result['clarification_question'],
                'agent_run_id' => $runId,
            ], 'Clarification required');
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => $request->user()->id,
            'raw_input' => $request->input,
            'detected_lang' => $result['detected_lang'] ?? 'english',
            'service_type' => $result['service_type'] ?? null,
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

        $this->agentTrace->log(
            $runId, 'intent', 1,
            ['input' => $request->input, 'lat' => $request->lat, 'lng' => $request->lng],
            $serviceRequest->toArray(),
            "Intent parsed successfully. Language: {$serviceRequest->detected_lang}. Complexity: {$serviceRequest->complexity}.",
            $result['confidence'] ?? 0,
            $durationMs
        );

        return $this->created([
            'id' => $serviceRequest->id,
            'agent_run_id' => $runId,
            'parsed_intent' => [
                'service_type' => $serviceRequest->service_type,
                'location' => $serviceRequest->location,
                'urgency' => $serviceRequest->urgency,
                'preferred_time' => $serviceRequest->requested_datetime,
                'budget_sensitivity' => $serviceRequest->budget_sensitivity,
                'complexity' => $serviceRequest->complexity,
                'detected_lang' => $serviceRequest->detected_lang,
                'confidence' => $serviceRequest->confidence,
            ],
            'clarification_question' => null,
        ], 'Service request created');
    }

    public function index(): JsonResponse
    {
        $requests = ServiceRequest::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return $this->paginated($requests);
    }

    public function show(int $id): JsonResponse
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())->findOrFail($id);

        return $this->success($serviceRequest);
    }
}
