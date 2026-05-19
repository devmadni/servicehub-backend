<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentTrace extends Model
{
    protected $fillable = [
        'run_id', 'agent_name', 'sequence', 'input_payload',
        'output_payload', 'reasoning', 'confidence', 'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'confidence' => 'float',
        ];
    }
}
