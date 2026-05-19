<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'area', 'lat', 'lng',
        'rating_avg', 'on_time_score', 'cancel_rate', 'experience_years',
        'specializations', 'capacity_current', 'risk_score', 'price_min',
        'status', 'warning_count',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'rating_avg' => 'float',
            'on_time_score' => 'float',
            'cancel_rate' => 'float',
            'risk_score' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function pricingQuotes(): HasMany
    {
        return $this->hasMany(PricingQuote::class);
    }

    public function reputations(): HasMany
    {
        return $this->hasMany(ProviderReputation::class);
    }
}
