@component('mail::message')
# Subscription activated

Hi {{ $subscription->user->name }},

Your payment has been verified and your **{{ $subscription->plan->full_name }}** subscription is now active
@if ($subscription->expires_at)
until **{{ $subscription->expires_at->format('F j, Y') }}**.
@else
— your account details will be sent shortly.
@endif

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
