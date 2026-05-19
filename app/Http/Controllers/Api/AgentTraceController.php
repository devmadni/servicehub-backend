<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgentTraceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AgentTraceController extends Controller
{
    use ApiResponse;

    public function __construct(private AgentTraceService $agentTrace) {}

    public function show(string $runId): JsonResponse
    {
        return $this->success([
            'run_id' => $runId,
            'steps' => $this->agentTrace->getTrace($runId),
        ]);
    }

    public function export(string $runId): JsonResponse
    {
        return $this->success([
            'run_id' => $runId,
            'exported_at' => now()->toDateTimeString(),
            'steps' => $this->agentTrace->export($runId),
        ], 'Trace exported');
    }
}
