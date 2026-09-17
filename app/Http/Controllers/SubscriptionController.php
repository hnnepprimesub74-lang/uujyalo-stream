<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PaymentProof;
use App\Models\Plan;
use App\Models\SaleSource;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function subscribe(Plan $plan): RedirectResponse
    {
        $user = auth()->user();

        $subscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', Subscription::STATUS_PENDING)
            ->first();

        if (! $subscription) {
            $websiteSource = SaleSource::firstOrCreate(
                ['name' => 'Website'],
                ['details' => 'Orders placed directly on the website checkout.']
            );

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_PENDING,
                'amount' => $plan->price,
                'sale_source_id' => $websiteSource->id,
            ]);
        }

        $isSharedAccount = (bool) $plan->product?->is_shared_account;

        return $isSharedAccount
            ? redirect()->route('subscriptions.pay', $subscription)
            : redirect()->route('subscriptions.details', $subscription);
    }

    public function details(Subscription $subscription): Response|RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        if ($subscription->plan->product?->is_shared_account) {
            return redirect()->route('subscriptions.pay', $subscription);
        }

        $subscription->load('plan.product');

        return Inertia::render('Subscriptions/Details', compact('subscription'));
    }

    public function storeDetails(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $subscription->update([
            'account_email' => $data['email'],
            'account_password' => $data['password'] ?? $subscription->account_password,
        ]);

        return redirect()->route('subscriptions.pay', $subscription);
    }

    public function pay(Subscription $subscription): Response
    {
        abort_unless($subscription->user_id === auth()->id(), 403);

        $subscription->load('plan.product');

        $instructions = AppSetting::get(
            'payment_instructions',
            "Bank Transfer: Account Name - Uujyalo Stream\nAccount No: 0000000000\nBank: Your Bank\n\nOr eSewa/Khalti Wallet: 98XXXXXXXX\n\nAfter payment, submit the reference number and screenshot below."
        );

        $qrCodePath = AppSetting::get('payment_qr_code');
        $qrCodeUrl = $qrCodePath ? Storage::disk('public')->url($qrCodePath) : null;

        $latestProof = $subscription->paymentProofs()->latest('id')->first();
        $needsInfoNote = $latestProof?->status === PaymentProof::STATUS_NEEDS_INFO
            ? $latestProof->note
            : null;
        $rejectionNote = $subscription->status === Subscription::STATUS_REJECTED
            && $latestProof?->status === PaymentProof::STATUS_REJECTED
            ? $latestProof->note
            : null;

        return Inertia::render('Subscriptions/Pay', compact('subscription', 'instructions', 'qrCodeUrl', 'needsInfoNote', 'rejectionNote'));
    }

    public function storeProof(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
        abort_unless(
            in_array($subscription->status, [Subscription::STATUS_PENDING, Subscription::STATUS_REJECTED]),
            403
        );

        $data = $request->validate([
            'payment_method' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'screenshot' => ['required', 'image', 'max:4096'],
            'screenshot_2' => ['nullable', 'image', 'max:4096'],
        ]);

        $path = $request->file('screenshot')->store('payment-proofs', 'public');
        $path2 = $request->hasFile('screenshot_2')
            ? $request->file('screenshot_2')->store('payment-proofs', 'public')
            : null;

        PaymentProof::create([
            'subscription_id' => $subscription->id,
            'user_id' => auth()->id(),
            'payment_method' => $data['payment_method'],
            'reference_no' => $data['reference_no'] ?? null,
            'customer_note' => $data['customer_note'] ?? null,
            'screenshot_path' => $path,
            'screenshot_path_2' => $path2,
            'status' => PaymentProof::STATUS_PENDING,
        ]);

        if ($subscription->status === Subscription::STATUS_REJECTED) {
            $subscription->update(['status' => Subscription::STATUS_PENDING]);
        }

        return redirect()->route('dashboard')->with('status', 'Payment proof submitted. We will verify it shortly.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
        abort_unless($subscription->status === Subscription::STATUS_PENDING, 403);
        abort_if(
            $subscription->paymentProofs()->where('status', PaymentProof::STATUS_PENDING)->exists(),
            403,
            'A payment proof for this order is already awaiting review.'
        );

        $subscription->delete();

        return redirect()->route('dashboard')->with('status', 'Order removed.');
    }
}
