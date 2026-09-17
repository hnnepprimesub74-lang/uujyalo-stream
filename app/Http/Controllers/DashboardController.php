<?php

namespace App\Http\Controllers;

use App\Models\PaymentProof;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $activeSubscriptions = $user->activeSubscriptions()
            ->with('plan.product', 'sharedAccount')
            ->get()
            ->map(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'product_name' => $subscription->plan->product?->name,
                'plan_name' => $subscription->plan->full_name,
                'account_email' => $subscription->displayAccountEmail(),
                'account_password' => $subscription->displayAccountPassword(),
                'total_days' => $subscription->total_days,
                'remaining_days' => $subscription->daysUntilExpiry(),
                'amount' => $subscription->amount,
                'expires_at' => $subscription->expires_at?->format('F j, Y'),
            ]);

        $subscriptions = $user->subscriptions()
            ->with('plan.product', 'paymentProofs')
            ->latest()
            ->get()
            ->map(function (Subscription $subscription) {
                $latestProof = $subscription->paymentProofs->sortByDesc('id')->first();
                $awaitingActivation = $subscription->isAwaitingActivation();
                $awaitingReview = $subscription->status === Subscription::STATUS_PENDING
                    && $latestProof?->status === PaymentProof::STATUS_PENDING;
                $needsInfo = $subscription->status === Subscription::STATUS_PENDING
                    && $latestProof?->status === PaymentProof::STATUS_NEEDS_INFO;
                $isRejected = $subscription->status === Subscription::STATUS_REJECTED;

                return [
                    'id' => $subscription->id,
                    'product_name' => $subscription->plan->product?->name,
                    'plan_name' => $subscription->plan->full_name,
                    'status' => $subscription->status,
                    'amount' => $subscription->amount,
                    'expires_at' => $subscription->expires_at?->format('M j, Y'),
                    'awaiting_activation' => $awaitingActivation,
                    'awaiting_review' => $awaitingReview,
                    'needs_info' => $needsInfo,
                    'is_rejected' => $isRejected,
                    'latest_proof_note' => $latestProof?->note,
                    'can_pay' => ($subscription->status === Subscription::STATUS_PENDING && ! $awaitingReview) || $isRejected,
                    'can_delete' => $subscription->status === Subscription::STATUS_PENDING && ! $awaitingReview,
                ];
            });

        return Inertia::render('Dashboard', compact('activeSubscriptions', 'subscriptions'));
    }
}
