@component('mail::message')
# Your subscription has expired

Hi {{ $subscription->user->name }},

Your **{{ $subscription->plan->full_name }}** subscription expired on **{{ $subscription->expires_at->format('F j, Y') }}**.

Renew now to restore your access.

@component('mail::button', ['url' => route('plans.index')])
Renew Subscription
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
