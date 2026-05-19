<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'raw_input', 'detected_lang', 'service_type', 'location',
        'user_lat', 'user_lng', 'urgency', 'budget_sensitivity', 'requested_datetime',
        'issue_severity', 'confidence', 'clarification_asked', 'complexity', 'agent_run_id',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'user_lat' => 'float',
            'user_lng' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
