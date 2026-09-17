<div style="display:flex;flex-direction:column;gap:0.75rem;">
    @forelse ($subscriptions as $subscription)
        <div style="border-radius:0.75rem;border:1px solid rgba(127,127,127,0.25);padding:1rem;">
            <p style="font-size:0.75rem;font-weight:600;color:#f59e0b;text-transform:uppercase;letter-spacing:0.05em;margin:0;">{{ $subscription->plan->product?->name }}</p>
            <p style="font-size:1rem;font-weight:700;margin:0.125rem 0 0;">{{ $subscription->plan->full_name }}</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;column-gap:1rem;row-gap:0.5rem;margin-top:0.75rem;font-size:0.875rem;">
                <div>
                    <div style="color:#9ca3af;">Account Email</div>
                    <div style="font-weight:500;word-break:break-all;">{{ $subscription->displayAccountEmail() ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#9ca3af;">Account Password</div>
                    <div style="font-weight:500;word-break:break-all;">{{ $subscription->displayAccountPassword() ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#9ca3af;">Total Days</div>
                    <div style="font-weight:500;">{{ $subscription->total_days }}</div>
                </div>
                <div>
                    <div style="color:#9ca3af;">Remaining Days</div>
                    <div style="font-weight:500;">{{ $subscription->daysUntilExpiry() }}</div>
                </div>
                <div>
                    <div style="color:#9ca3af;">Amount Paid</div>
                    <div style="font-weight:500;">NPR {{ number_format($subscription->amount, 0) }}</div>
                </div>
                <div>
                    <div style="color:#9ca3af;">Expires At</div>
                    <div style="font-weight:600;color:#34d399;">{{ $subscription->expires_at->format('F j, Y') }}</div>
                </div>
            </div>
        </div>
    @empty
        <p style="font-size:0.875rem;color:#9ca3af;text-align:center;padding:1.5rem 0;">No active subscriptions.</p>
    @endforelse
</div>
