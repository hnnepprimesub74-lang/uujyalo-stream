<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Coupon extends Model
{
    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENTAGE = 'percentage';

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon) {
            if ($coupon->code) {
                $coupon->code = Str::upper(Str::of($coupon->code)->trim());
            }
        });
    }

    public function isWithinDateRange(): bool
    {
        $today = Carbon::today();

        if ($this->starts_at && $today->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $today->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function meetsMinimumOrder(float $orderAmount): bool
    {
        return $this->min_order_amount === null || $orderAmount >= (float) $this->min_order_amount;
    }

    public function isRedeemableFor(float $orderAmount): bool
    {
        return $this->is_active && $this->isWithinDateRange() && $this->meetsMinimumOrder($orderAmount);
    }

    public function calculateDiscount(float $orderAmount): float
    {
        $discount = $this->type === self::TYPE_PERCENTAGE
            ? $orderAmount * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $orderAmount), 2);
    }
}
