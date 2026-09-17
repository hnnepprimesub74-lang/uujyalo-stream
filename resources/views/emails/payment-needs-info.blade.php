@component('mail::message')
# We need a bit more info

Hi {{ $paymentProof->user->name }},

We're reviewing your payment for the **{{ $paymentProof->subscription->plan->full_name }}** plan, but need something from you before we can approve it.

@if ($paymentProof->note)
**What's needed:** {{ $paymentProof->note }}
@endif

Please go to your dashboard and resubmit your payment proof (you can update the payment method, reference number, or attach a new screenshot).

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
