<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PricingQuote extends Model
{
    /** @use HasFactory<\Database\Factories\PricingQuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id', 'provider_id', 'base_rate', 'visit_fee', 'distance_cost',
        'urgency_adj', 'surge_factor', 'loyalty_discount', 'total', 'provider_net',
        'is_budget_tier', 'alt_quote_id',
    ];

    protected function casts(): array
    {
        return [
            'surge_factor' => 'float',
            'is_budget_tier' => 'boolean',
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

    public function altQuote(): BelongsTo
    {
        return $this->belongsTo(PricingQuote::class, 'alt_quote_id');
    }
}
