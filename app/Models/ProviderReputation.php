<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderReputation extends Model
{
    protected $fillable = [
        'provider_id', 'score_before', 'score_after', 'trigger', 'booking_id', 'delta',
    ];

    protected function casts(): array
    {
        return [
            'score_before' => 'float',
            'score_after' => 'float',
            'delta' => 'float',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
