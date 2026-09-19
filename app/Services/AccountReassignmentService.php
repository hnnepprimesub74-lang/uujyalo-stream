<?php

namespace App\Services;

use App\Mail\AccountReadyMail;
use App\Models\AccountReassignmentLog;
use App\Models\SharedAccount;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;

class AccountReassignmentService
{
    public const WARNING_DAYS = 3;

    /**
     * Find every active subscription on a shared-account product that needs
     * a (re)assignment: either it has no account yet (approved while no
     * stock was available) or its current account is expiring/expired within
     * the warning window. Does not attempt reassignment — just the worklist.
     */
    public function subscriptionsNeedingAttention()
    {
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
                || $subscription->sharedAccount?->needsRotation(self::WARNING_DAYS));
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

        foreach ($this->subscriptionsNeedingAttention() as $subscription) {
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

            if ($isFirstAssignment && ! str_ends_with((string) $subscription->user->email, '@no-email.local')) {
                Mail::to($subscription->user->email)->send(new AccountReadyMail($subscription));
            }

            AccountReassignmentLog::create([
                'subscription_id' => $subscription->id,
                'old_shared_account_id' => $oldAccount?->id,
                'new_shared_account_id' => $newAccount->id,
            ]);

            $reassigned++;
        }

        $this->retireDrainedAccounts(array_keys($touchedAccountIds));

        return ['reassigned' => $reassigned, 'stuck' => $stuck];
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
