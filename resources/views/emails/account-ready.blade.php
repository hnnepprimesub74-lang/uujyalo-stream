@component('mail::message')
# Your account is ready

Hi {{ $subscription->user->name }},

Your **{{ $subscription->plan->product?->name }} — {{ $subscription->plan->full_name }}** account has been set up and is ready to use
@if ($subscription->expires_at)
until **{{ $subscription->expires_at->format('F j, Y') }}**.
@else
.
@endif

@component('mail::panel')
**Account Email:** {{ $subscription->displayAccountEmail() ?? '—' }}<br>
**Account Password:** {{ $subscription->displayAccountPassword() ?? '—' }}
@endcomponent

@if (! empty($subscription->plan->usage_rules))
**Rules while using Account:**

@foreach ($subscription->plan->usage_rules as $rule)
- {{ $rule }}
@endforeach
@endif

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
