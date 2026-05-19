<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Services\OrchestratorService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrchestrateController extends Controller
{
    use ApiResponse;

    public function __construct(private OrchestratorService $orchestrator) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'input' => 'required|string|max:1000',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $result = $this->orchestrator->orchestrate(
            $request->input,
            (float) $request->lat,
            (float) $request->lng,
            $request->user()
        );

        if (! $result['proceed']) {
            return $this->success([
                'run_id' => $result['run_id'],
                'proceed' => false,
                'clarification_question' => $result['clarification_question'],
            ], 'Clarification required');
        }

        $intent = $result['intent'];

        $serviceRequest = ServiceRequest::create([
            'user_id' => $request->user()->id,
            'raw_input' => $request->input,
            'detected_lang' => $intent['detected_lang'],
            'service_type' => $intent['service_type'],
            'location' => $intent['location'],
            'user_lat' => $request->lat,
            'user_lng' => $request->lng,
            'urgency' => in_array($intent['urgency'], ['low', 'normal', 'high', 'emergency']) ? $intent['urgency'] : 'normal',
            'budget_sensitivity' => in_array($intent['budget_sensitivity'], ['normal', 'high']) ? $intent['budget_sensitivity'] : 'normal',
            'requested_datetime' => $intent['preferred_time'],
            'issue_severity' => in_array($intent['issue_severity'], ['low', 'medium', 'high']) ? $intent['issue_severity'] : 'low',
            'confidence' => $intent['confidence'],
            'complexity' => in_array($intent['complexity'], ['basic', 'intermediate', 'complex']) ? $intent['complexity'] : 'basic',
            'agent_run_id' => $result['run_id'],
        ]);

        return $this->created([
            'run_id' => $result['run_id'],
            'service_request_id' => $serviceRequest->id,
            'intent' => $intent,
            'providers' => $result['providers'],
            'clarification_question' => null,
        ], 'Orchestration complete');
    }
}
