@component('mail::message')
# Payment could not be verified

Hi {{ $paymentProof->user->name }},

We were unable to verify your payment for the **{{ $paymentProof->subscription->plan->full_name }}** plan.

@if ($paymentProof->note)
**Reason:** {{ $paymentProof->note }}
@endif

Please submit a valid payment proof to activate your subscription.

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
