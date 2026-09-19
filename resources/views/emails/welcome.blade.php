@component('mail::message')
# Welcome to {{ config('app.name') }}

Hi {{ $user->name }},

Thanks for creating an account with {{ config('app.name') }} — Nepal's destination for premium digital
subscriptions, delivered instantly with easy local payment.

@component('mail::button', ['url' => route('home')])
Browse Products
@endcomponent

If you have any questions, our team is always ready to help.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
