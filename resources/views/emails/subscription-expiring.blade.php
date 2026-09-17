@component('mail::message')
# Your subscription is expiring soon

Hi {{ $subscription->user->name }},

Your **{{ $subscription->plan->full_name }}** subscription will expire on **{{ $subscription->expires_at->format('F j, Y') }}**.

Renew now to avoid losing access.

@component('mail::button', ['url' => route('plans.index')])
Renew Subscription
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
