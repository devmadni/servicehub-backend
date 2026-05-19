<?php

namespace App\Services;

use App\Models\AgentTrace;

class AgentTraceService
{
    public function log(
        string $runId,
        string $agentName,
        int $sequence,
        array $input,
        array $output,
        string $reasoning,
        float $confidence,
        int $durationMs
    ): void {
        AgentTrace::create([
            'run_id' => $runId,
            'agent_name' => $agentName,
            'sequence' => $sequence,
            'input_payload' => $input,
            'output_payload' => $output,
            'reasoning' => $reasoning,
            'confidence' => $confidence,
            'duration_ms' => $durationMs,
        ]);
    }

    public function getTrace(string $runId): array
    {
        return AgentTrace::where('run_id', $runId)
            ->orderBy('sequence')
            ->get()
            ->toArray();
    }

    public function export(string $runId): array
    {
        return AgentTrace::where('run_id', $runId)
            ->orderBy('sequence')
            ->get()
            ->map(fn ($t) => [
                'agent' => $t->agent_name,
                'step' => $t->sequence,
                'reasoning' => $t->reasoning,
                'confidence' => $t->confidence,
                'input' => $t->input_payload,
                'output' => $t->output_payload,
                'duration' => $t->duration_ms.'ms',
            ])->toArray();
    }
}
