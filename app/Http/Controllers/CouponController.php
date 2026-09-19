<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function store(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
        abort_unless(
            in_array($subscription->status, [Subscription::STATUS_PENDING, Subscription::STATUS_REJECTED]),
            403
        );

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $orderAmount = (float) $subscription->plan->price;

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($data['code']))])
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => 'This coupon code does not exist.']);
        }

        if (! $coupon->is_active || ! $coupon->isWithinDateRange()) {
            throw ValidationException::withMessages(['code' => 'This coupon code has expired or is not active.']);
        }

        if (! $coupon->meetsMinimumOrder($orderAmount)) {
            throw ValidationException::withMessages([
                'code' => 'This coupon requires a minimum order of NPR '.number_format((float) $coupon->min_order_amount, 0).'.',
            ]);
        }

        $discount = $coupon->calculateDiscount($orderAmount);

        $subscription->update([
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
            'amount' => $orderAmount - $discount,
        ]);

        return redirect()->route('subscriptions.pay', $subscription)
            ->with('status', "Coupon \"{$coupon->code}\" applied — you saved NPR ".number_format($discount, 0).'.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === auth()->id(), 403);
        abort_unless(
            in_array($subscription->status, [Subscription::STATUS_PENDING, Subscription::STATUS_REJECTED]),
            403
        );

        $subscription->update([
            'coupon_code' => null,
            'discount_amount' => 0,
            'amount' => $subscription->plan->price,
        ]);

        return redirect()->route('subscriptions.pay', $subscription);
    }
}
