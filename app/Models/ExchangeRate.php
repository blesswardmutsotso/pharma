<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    /**
     * Every non-USD currency usable elsewhere in the system (Purchase Order /
     * Sales Order currency selects) must have an entry here.
     */
    public const CONVERTIBLE_CURRENCIES = ['ZWG'];

    protected $fillable = [
        'currency_code',
        'rate_to_usd',
        'updated_by',
    ];

    protected $casts = [
        'rate_to_usd' => 'decimal:6',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Units of $currencyCode per 1 USD, or null if no rate has been set yet.
     */
    public static function rateFor(string $currencyCode): ?float
    {
        if ($currencyCode === 'USD') {
            return 1.0;
        }

        $rate = static::where('currency_code', $currencyCode)->value('rate_to_usd');

        return $rate !== null ? (float) $rate : null;
    }

    /**
     * Convert an amount in $currencyCode to its USD equivalent using the
     * currently configured rate. Falls back to a 1:1 passthrough (no
     * conversion) when no rate has been configured yet, so figures aren't
     * silently corrupted before an admin sets the rate — the fallback simply
     * reproduces the system's pre-exchange-rate behaviour.
     */
    public static function toUsd(float $amount, string $currencyCode): float
    {
        if ($currencyCode === 'USD') {
            return $amount;
        }

        $rate = static::rateFor($currencyCode);

        if (!$rate || $rate <= 0) {
            return $amount;
        }

        return round($amount / $rate, 2);
    }
}
