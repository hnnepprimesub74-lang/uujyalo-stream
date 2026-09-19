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

        $canReview = fn (Subscription $subscription) => in_array($subscription->status, [
            Subscription::STATUS_ACTIVE, Subscription::STATUS_EXPIRED,
        ], true);

        $reviewData = fn (Subscription $subscription) => $subscription->review ? [
            'rating' => $subscription->review->rating,
            'comment' => $subscription->review->comment,
        ] : null;

        $activeSubscriptions = $user->activeSubscriptions()
            ->with('plan.product', 'sharedAccount', 'review')
            ->get()
            ->map(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'product_name' => $subscription->plan->product?->name,
                'plan_name' => $subscription->plan->full_name,
                'usage_rules' => $subscription->plan->usage_rules ?? [],
                'account_email' => $subscription->displayAccountEmail(),
                'account_password' => $subscription->displayAccountPassword(),
                'total_days' => $subscription->total_days,
                'remaining_days' => $subscription->daysUntilExpiry(),
                'amount' => $subscription->amount,
                'expires_at' => $subscription->expires_at?->format('F j, Y'),
                'can_review' => $canReview($subscription),
                'review' => $reviewData($subscription),
            ]);

        $subscriptions = $user->subscriptions()
            ->with('plan.product', 'paymentProofs', 'review')
            ->latest()
            ->get()
            ->map(function (Subscription $subscription) use ($canReview, $reviewData) {
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
                    'can_review' => $canReview($subscription),
                    'review' => $reviewData($subscription),
                ];
            });

        return Inertia::render('Dashboard', compact('activeSubscriptions', 'subscriptions'));
    }
}
