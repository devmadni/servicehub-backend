<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_ref', 'user_id', 'provider_id', 'service_request_id', 'pricing_quote_id',
        'slot_datetime', 'slot_end_datetime', 'complexity', 'status', 'simulated',
        'confirmed_at', 'completed_at', 'cancellation_reason', 'agent_run_id',
    ];

    protected function casts(): array
    {
        return [
            'slot_datetime' => 'datetime',
            'slot_end_datetime' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'simulated' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function pricingQuote(): BelongsTo
    {
        return $this->belongsTo(PricingQuote::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }
}
