<?php

namespace App\Services;

use App\Mail\AccountUpdatedMail;
use App\Models\AccountReassignmentLog;
use App\Models\NotificationLog;
use App\Models\SharedAccount;
use App\Models\Subscription;
use App\Services\Sms\SmsChannelInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AccountReassignmentService
{
    public function __construct(protected SmsChannelInterface $sms)
    {
    }

    /**
     * How early a subscription shows up on the "Waiting for a New Account"
     * list / sidebar badge — a heads-up before anything actually happens.
     */
    public const WARNING_DAYS = 3;

    /**
     * How close to actual expiry (0 = already expired) an account has to be
     * before run() will really swap the customer onto a new one. Kept
     * separate from WARNING_DAYS so customers keep using their current
     * account right up until it stops working, instead of being moved
     * early just because it's inside the warning window.
     */
    public const REASSIGN_AT_DAYS = 0;

    /**
     * Find every active subscription on a shared-account product that needs
     * a (re)assignment: either it has no account yet (approved while no
     * stock was available) or its current account is expiring/expired within
     * $withinDays. Does not attempt reassignment — just the worklist.
     */
    public function subscriptionsNeedingAttention(?int $withinDays = null)
    {
        $withinDays ??= self::WARNING_DAYS;

        return Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($query) {
                // Never activated yet (awaiting its first account) has no
                // expiry to compare against; an already-running one must
                // still be current.
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('plan.product', fn ($query) => $query->where('is_shared_account', true))
            ->with(['user', 'plan.product', 'sharedAccount'])
            ->get()
            ->filter(fn (Subscription $subscription) => $subscription->shared_account_id === null
                || $subscription->sharedAccount?->needsRotation($withinDays));
    }

    /**
     * Attempt to move every subscription that needs it onto a healthy account.
     * Returns ['reassigned' => int, 'stuck' => int].
     */
    public function run(): array
    {
        $reassigned = 0;
        $stuck = 0;
        $touchedAccountIds = [];

        foreach ($this->subscriptionsNeedingAttention(self::REASSIGN_AT_DAYS) as $subscription) {
            $oldAccount = $subscription->sharedAccount;

            if ($oldAccount) {
                $touchedAccountIds[$oldAccount->id] = true;
            }

            $newAccount = $this->findReplacement($oldAccount, $subscription->plan->product_id, $subscription->slots_used);

            if (! $newAccount) {
                $stuck++;

                continue;
            }

            $update = ['shared_account_id' => $newAccount->id];
            $isFirstAssignment = $subscription->starts_at === null;

            if ($isFirstAssignment) {
                // First-ever assignment for this subscription — the
                // countdown starts now, not back at payment approval.
                $startsAt = now();

                $update['starts_at'] = $startsAt;
                $update['expires_at'] = $startsAt->copy()->addDays($subscription->total_days);
                $update['days_recharged'] = $subscription->total_days;
                $update['next_recharge_date'] = null;
            }

            $subscription->update($update);

            $log = AccountReassignmentLog::create([
                'subscription_id' => $subscription->id,
                'old_shared_account_id' => $oldAccount?->id,
                'new_shared_account_id' => $newAccount->id,
            ]);

            $log->update(['customer_notified' => $this->notifyCustomer($subscription)]);

            $reassigned++;
        }

        $this->retireDrainedAccounts(array_keys($touchedAccountIds));

        return ['reassigned' => $reassigned, 'stuck' => $stuck];
    }

    /**
     * Tell the customer their login changed — email if they have a real
     * address on file, SMS otherwise. Returns whether it went out successfully.
     */
    protected function notifyCustomer(Subscription $subscription): bool
    {
        $user = $subscription->user;

        if ($user->hasRealEmail()) {
            try {
                Mail::to($user->email)->send(new AccountUpdatedMail($subscription));
            } catch (Throwable $e) {
                Log::error('Account reassignment email failed', ['error' => $e->getMessage(), 'subscription_id' => $subscription->id]);

                NotificationLog::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'channel' => NotificationLog::CHANNEL_EMAIL,
                    'type' => NotificationLog::TYPE_ACCOUNT_REASSIGNED,
                    'status' => NotificationLog::STATUS_FAILED,
                    'message' => $e->getMessage(),
                ]);

                return false;
            }

            NotificationLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'channel' => NotificationLog::CHANNEL_EMAIL,
                'type' => NotificationLog::TYPE_ACCOUNT_REASSIGNED,
                'status' => NotificationLog::STATUS_SENT,
            ]);

            return true;
        }

        if (empty($user->phone)) {
            NotificationLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'channel' => NotificationLog::CHANNEL_SMS,
                'type' => NotificationLog::TYPE_ACCOUNT_REASSIGNED,
                'status' => NotificationLog::STATUS_FAILED,
                'response' => 'No phone number on file.',
            ]);

            return false;
        }

        $message = 'Your account has been updated. Please check our website to get your new ID and password.';
        $result = $this->sms->send($user->phone, $message);

        NotificationLog::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'channel' => NotificationLog::CHANNEL_SMS,
            'type' => NotificationLog::TYPE_ACCOUNT_REASSIGNED,
            'status' => $result['success'] ? NotificationLog::STATUS_SENT : NotificationLog::STATUS_FAILED,
            'message' => $message,
            'response' => $result['response'] ?? null,
        ]);

        return (bool) $result['success'];
    }

    protected function findReplacement(?SharedAccount $oldAccount, int $productId, int $slotsNeeded): ?SharedAccount
    {
        return SharedAccount::where('product_id', $productId)
            ->when($oldAccount, fn ($query) => $query->where('id', '!=', $oldAccount->id))
            ->where('is_active', true)
            ->get()
            ->first(fn (SharedAccount $candidate) => $candidate->availableSlots() >= $slotsNeeded
                && ! $candidate->needsRotation(self::WARNING_DAYS));
    }

    protected function retireDrainedAccounts(array $accountIds): void
    {
        foreach (SharedAccount::whereIn('id', $accountIds)->get() as $account) {
            if ($account->usedSlots() === 0 && $account->is_active) {
                $account->update(['is_active' => false]);
            }
        }
    }
}
