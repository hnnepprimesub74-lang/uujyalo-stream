<?php

namespace App\Services;

use App\Models\RechargeLog;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

class RechargeService
{
    /**
     * How many days of account access a given payment covers,
     * based on the plan's cost-to-us per month.
     */
    public function daysForAmount(float $amount, float $monthlyCost): int
    {
        if ($monthlyCost <= 0) {
            return 0;
        }

        return (int) round(($amount / $monthlyCost) * 30);
    }

    /**
     * Apply a recharge (top-up) to a subscription: extends days_recharged,
     * schedules the next recharge date (or clears it once fully covered),
     * and records a log entry.
     */
    public function applyRecharge(Subscription $subscription, float $amount, ?string $note = null): RechargeLog
    {
        $monthlyCost = (float) ($subscription->plan->monthly_cost ?? 0);
        $daysAdded = $this->daysForAmount($amount, $monthlyCost);

        $totalDays = $subscription->total_days ?? $subscription->plan->duration_days;
        $newDaysRecharged = min($totalDays, $subscription->days_recharged + $daysAdded);
        $today = Carbon::today();

        $subscription->update([
            'total_days' => $totalDays,
            'days_recharged' => $newDaysRecharged,
            'last_recharge_date' => $today,
            'next_recharge_date' => $newDaysRecharged < $totalDays ? $today->copy()->addDays($daysAdded) : null,
        ]);

        return RechargeLog::create([
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'days_added' => $daysAdded,
            'recharged_at' => $today,
            'recharged_by' => auth()->id(),
            'note' => $note,
        ]);
    }
}
