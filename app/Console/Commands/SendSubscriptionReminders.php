<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Subscription;
use App\Services\SubscriptionNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Send expiring-soon and expired reminders to subscribers via email and SMS, and mark expired subscriptions.';

    public function handle(SubscriptionNotifier $notifier): int
    {
        $reminderDays = (int) AppSetting::get('reminder_days_before_expiry', 3);

        $expiringSoon = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('expiring_reminder_sent', false)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($reminderDays)])
            ->with(['user', 'plan'])
            ->get();

        foreach ($expiringSoon as $subscription) {
            $notifier->notifyExpiringSoon($subscription);
            $subscription->update(['expiring_reminder_sent' => true]);
            $this->info("Expiring reminder sent for subscription #{$subscription->id}");
        }

        $justExpired = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with(['user', 'plan'])
            ->get();

        foreach ($justExpired as $subscription) {
            $subscription->update(['status' => Subscription::STATUS_EXPIRED]);

            if (! $subscription->expired_reminder_sent) {
                $notifier->notifyExpired($subscription);
                $subscription->update(['expired_reminder_sent' => true]);
                $this->info("Expired reminder sent for subscription #{$subscription->id}");
            }
        }

        $this->info('Subscription reminder run complete.');

        return self::SUCCESS;
    }
}
