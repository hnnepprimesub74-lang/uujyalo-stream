<?php

namespace App\Console\Commands;

use App\Filament\Support\SubscriptionTableActions;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireUnrechargedSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-unrecharged';

    protected $description = 'Expire active subscriptions whose recharge has been overdue for longer than the grace period.';

    public function handle(): int
    {
        $today = Carbon::today();
        $graceCutoff = $today->copy()->subDays(SubscriptionTableActions::RECHARGE_GRACE_DAYS);

        $overdue = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereColumn('days_recharged', '<', 'total_days')
            ->whereNotNull('next_recharge_date')
            ->where('next_recharge_date', '<', $graceCutoff->toDateString())
            ->get();

        foreach ($overdue as $subscription) {
            $subscription->update([
                'status' => Subscription::STATUS_EXPIRED,
                'admin_note' => trim(($subscription->admin_note ?? '')."\nAuto-expired on {$today->toDateString()}: recharge overdue beyond the ".SubscriptionTableActions::RECHARGE_GRACE_DAYS.'-day grace period.'),
            ]);

            $this->info("Subscription #{$subscription->id} expired (recharge overdue).");
        }

        $this->info('Recharge grace-period check complete.');

        return self::SUCCESS;
    }
}
